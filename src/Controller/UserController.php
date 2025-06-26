<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class UserController extends HelperController
{
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, UserPasswordHasherInterface $passwordEncoder)
    {
        parent::__construct($entityManager, $serializer);
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/api/register', methods: ["POST"])]
    public function registerUser(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['email', 'password', 'first_name', 'last_name', "username"])) {
            return $this->res("Missing key in payload, please check your request, 'email', 'password', 'first_name', 'last_name', 'username'", null, 400);
        }
        $roles = ["ROLE_USER"];

        $email_exist = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $payload['email']]);
        if ($email_exist) {
            return $this->res("Email already exist", 400);
        }

        $user = new User();
        $user
            ->setEmail($payload['email'])
            ->setPassword($this->passwordEncoder->hashPassword($user, $payload['password']))
            ->setRoles($roles)
            ->setFirstName($payload['first_name'])
            ->setLastName($payload['last_name'])
            ->setAlias($payload['username']);


        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->success($user, ["readData"], 201);
    }

    #[Route('/api/user', methods: ["PUT"])]
    public function updateUser(Request $request) {
        $payload = json_decode($request->get("payload"), true);

        $user = $this->getUser();
        if (!$user) {
            return $this->res("User not found", null, 404);
        }

        $file = $request->files->get('picture');
        if ($file) {
            $picture_path = $this->getParameter('picture_directory');
            $picture_name = $file->getClientOriginalName();
            $extension = pathinfo($picture_path, PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png'])) {
                return $this->res("Invalid picture format (jpg, jpeg, png)", 400);
            }

            if ($file->getSize() == 0) {
                return $this->res("Empty file", 400);
            }

            if ($file->getSize() > $_ENV['MAX_PICTURE_SIZE']) {
                return $this->res("File too big", 400);
            }

            $file_name = md5(uniqid()) . '.' . $extension;
            $file->move($picture_path, $file_name);
            $user->setPicturePath($picture_path . '/' . $file_name);
        }

        if (isset($payload['first_name'])) {
            $user->setFirstName($payload['first_name']);
        }

        if (isset($payload['last_name'])) {
            $user->setLastName($payload['last_name']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->success($user, ["readData"]);
    }

    #[Route('/api/user/search', methods: ["GET"])]
    public function searchUser(Request $request)
    {
        // we want to search user by first name or last name
        $search = $request->query->get('query');
        $users = $this->entityManager->getRepository(User::class)->search($search);

        return $this->success($users);
    }


    #[Route('/api/user/me', methods: ["GET"])]
    public function index(): Response
    {
        return $this->success($this->getUser(), ["readData"]);
    }

    #[Route('/api/user/username/free', methods: ["GET"])]
    public function usernameIsFree(Request $request): Response
    {
        $username = $request->query->get('username');
        if (!$username) {
            return $this->res("Username is required", null, 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['alias' => $username]);
        if ($user) {
            return $this->res("Username already taken", null, 400);
        }

        return $this->res("Username is free", null, 200);
    }

    #[Route('/api/user/picture/{user}', methods: ["GET"])]
    public function getPicture(User $user)
    {
        if ($user->getPicturePath()) {
            $picturePath = $user->getPicturePath();

            $file = file_get_contents($picturePath);
            if ($file !== false) {
                $mimeType = mime_content_type($picturePath);
                $response = new Response($file);
                $response->headers->set('Content-Type', $mimeType);
                $response->headers->set('Content-Disposition', 'inline; filename="' . basename($picturePath) . '"');
                return $response;
            }
        }
        return null;
    }
}
