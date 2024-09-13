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
        $file = $request->files->get('file');
        $payload = json_decode($request->request->get('payload'), true);

        $audio_path = $this->getParameter('audio_directory');

        // check if the file is an audio file
        $audio_file = $file->getClientOriginalName();
        $audio_file_extension = pathinfo($audio_file, PATHINFO_EXTENSION);
        if (!in_array($audio_file_extension, ['mp3', 'wav'])) {
            return $this->res("Invalid audio file (MP3, WAV)", 400);
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
        $file->move($audio_path, $file_name);

        $audio = new Audio();
        $audio
            ->setTitle($payload['title'])
            ->setOriginalName($audio_file)
            ->setPath($audio_path . '/' . $file_name)
            ->setDuration($duration)
            ->setFileSize($file->getSize())
            ->setUser($this->getUser());

        $this->entityManager->persist($audio);
        $this->entityManager->flush();


        return $this->success($audio, ["readData"], 201);
    }

    #[Route('/api/audio', methods: ["GET"])]
    public function getAudios(Request $request)
    {
        $pdo = new NewPDO();
        $audios = $pdo->fetch("SELECT * FROM audio WHERE user_id = ? AND is_deleted IS NOT TRUE", [$this->getUser()->getId()]);
        return $this->success($audios);
    }
}
