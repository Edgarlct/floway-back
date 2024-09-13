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
            ->setLastName($payload['last_name']);


        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->success($user, ["readData"], 201);
    }

    #[Route('/api/user/search', methods: ["GET"])]
    public function searchUser(Request $request)
    {
        // we want to search user by first name or last name
        $search = $request->query->get('query');
        $users = $this->entityManager->getRepository(User::class)->search($search);

        return $this->success($users);
    }


    #[Route('/', methods: ["GET"])]
    public function index(): Response
    {
        return $this->success("Hello World");
    }
}
