<?php

namespace App\Controller;

use App\Entity\Audio;
use App\Tools\NewPDO;
use getID3;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AudioController extends HelperController
{

    #[Route('/api/audio', methods: ["POST"])]
    public function saveAudio(Request $request)
    {
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_execution_time', '300');

        $file = $request->files->get('file');
        $payload = json_decode($request->request->get('payload'), true);

        $audio_path = $this->getParameter('audio_directory');

        // check if the file is an audio file
        $audio_file = $file->getClientOriginalName();
        $audio_file_extension = pathinfo($audio_file, PATHINFO_EXTENSION);
        if (!in_array($audio_file_extension, ['mp3', 'wav', 'm4a'])) {
            return $this->res("Invalid audio file (MP3, WAV, m4a)", 400);
        }

        // check if the file is not empty
        if ($file->getSize() == 0) {
            return $this->res("Empty audio file", 400);
        }

        // check if the file is not too big
        if ($file->getSize() > $_ENV['MAX_AUDIO_SIZE']) {
            return $this->res("Audio file too big", 400);
        }

        // get duration of the audio file
        $getID3 = new getID3;

        // Analyze the file
        $fileInfo = $getID3->analyze($file->getPathname());
        $duration = 0;
        if (isset($fileInfo['playtime_seconds'])) $duration = $fileInfo['playtime_seconds'];

        $file_name = md5(uniqid()) . '.' . $audio_file_extension;
        $size = $file->getSize();
        $mime_type = $file->getMimeType();
        $file->move($audio_path, $file_name);

        $audio = new Audio();
        $audio
            ->setTitle($payload['title'])
            ->setOriginalName($audio_file)
            ->setPath($audio_path . '/' . $file_name)
            ->setDuration($duration)
            ->setFileSize($size)
            ->setMimeType($mime_type)
            ->setUser($this->getUser());

        $this->entityManager->persist($audio);
        $this->entityManager->flush();


        return $this->success($audio, ["readData"], 201);
    }

    #[Route('/api/audio', methods: ["GET"])]
    public function getAudios(Request $request)
    {
        $pdo = new NewPDO();
        $audios = $pdo->fetch("SELECT id, title, duration, file_size, original_name FROM audio WHERE user_id = ? AND is_deleted IS NOT TRUE", [$this->getUser()->getId()]);
        return $this->success($audios);
    }

    #[Route('/api/audio/{id}', methods: ["GET"])]
    public function getAudio($id)
    {
        $pdo = new NewPDO();
        $audio = $pdo->fetch("SELECT path, mime_type FROM audio WHERE id = ? AND user_id = ? AND is_deleted IS NOT TRUE", [$id, $this->getUser()->getId()]);
        if (empty($audio)) return $this->res("Audio not found", 404);

        // return the audio file
        $audio_path = $audio[0]['path'];
        // get extension
        $ext = pathinfo($audio_path, PATHINFO_EXTENSION);
        if (!in_array($ext, ['mp3', 'wav', 'm4a'])) {
            return $this->res("Invalid audio file", 400);
        }

        // Définir le Content-Type en fonction de l'extension
        $content_types = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4'
        ];

        $audio_file = file_get_contents($audio_path);
        $response = new Response($audio_file);
        $response->headers->set('Content-Type', $audio[0]["mime_type"] ?? $content_types[$ext]);
        return $response;
    }
}
