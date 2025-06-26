<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user',
        description: 'Create a new user account',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    'email' => new OA\Property(property: 'email', type: 'string', format: 'email', description: 'User email address', example: 'user@example.com'),
                    'password' => new OA\Property(property: 'password', type: 'string', description: 'User password', example: 'securePassword123'),
                    'first_name' => new OA\Property(property: 'first_name', type: 'string', description: 'User first name', example: 'John'),
                    'last_name' => new OA\Property(property: 'last_name', type: 'string', description: 'User last name', example: 'Doe'),
                    'username' => new OA\Property(property: 'username', type: 'string', description: 'User username/alias', example: 'johndoe')
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        'id' => new OA\Property(property: 'id', type: 'integer', example: 1),
                        'email' => new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        'first_name' => new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                        'last_name' => new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                        'username' => new OA\Property(property: 'username', type: 'string', example: 'johndoe')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Missing parameters or email already exists',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Email already exist')
                    ]
                )
            )
        ]
    )]
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
    #[OA\Put(
        path: '/api/user',
        summary: 'Update user profile',
        description: 'Update current user profile information and picture',
        tags: ['User'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: [
                'multipart/form-data' => new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        properties: [
                            'payload' => new OA\Property(
                                property: 'payload',
                                type: 'string',
                                description: 'JSON payload with user data',
                                example: '{"first_name": "John", "last_name": "Doe"}'
                            ),
                            'picture' => new OA\Property(
                                property: 'picture',
                                type: 'string',
                                format: 'binary',
                                description: 'Profile picture file (jpg, jpeg, png)'
                            )
                        ],
                        type: 'object'
                    )
                )
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        'id' => new OA\Property(property: 'id', type: 'integer', example: 1),
                        'email' => new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        'first_name' => new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                        'last_name' => new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                        'picture_path' => new OA\Property(property: 'picture_path', type: 'string', example: '/uploads/abc123.jpg')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Invalid file format or size',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Invalid picture format')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            )
        ]
    )]
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
    #[OA\Get(
        path: '/api/user/search',
        summary: 'Search users',
        description: 'Search users by first name or last name',
        tags: ['User'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'query',
                description: 'Search query for first name or last name',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'John')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search results retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            'id' => new OA\Property(property: 'id', type: 'integer', example: 1),
                            'first_name' => new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                            'last_name' => new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                            'username' => new OA\Property(property: 'username', type: 'string', example: 'johndoe')
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function searchUser(Request $request)
    {
        // we want to search user by first name or last name
        $search = $request->query->get('query');
        $users = $this->entityManager->getRepository(User::class)->search($search);

        return $this->success($users);
    }


    #[Route('/api/user/me', methods: ["GET"])]
    #[OA\Get(
        path: '/api/user/me',
        summary: 'Get current user profile',
        description: 'Get the authenticated user\'s profile information',
        tags: ['User'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'id' => new OA\Property(property: 'id', type: 'integer', example: 1),
                        'email' => new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        'first_name' => new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                        'last_name' => new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                        'username' => new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                        'picture_path' => new OA\Property(property: 'picture_path', type: 'string', example: '/uploads/abc123.jpg')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function index(): Response
    {
        return $this->success($this->getUser(), ["readData"]);
    }

    #[Route('/api/user/username/free', methods: ["GET"])]
    #[OA\Get(
        path: '/api/user/username/free',
        summary: 'Check username availability',
        description: 'Check if a username is available for registration',
        tags: ['User'],
        parameters: [
            new OA\Parameter(
                name: 'username',
                description: 'Username to check availability',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'johndoe')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Username is available',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Username is free')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Username not available or missing parameter',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Username already taken')
                    ]
                )
            )
        ]
    )]
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
    #[OA\Get(
        path: '/api/user/picture/{user}',
        summary: 'Get user profile picture',
        description: 'Retrieve a user\'s profile picture',
        tags: ['User'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile picture retrieved successfully',
                content: new OA\MediaType(
                    mediaType: 'image/*',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User or picture not found'
            )
        ]
    )]
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
