<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\FriendNotificationSettings;
use App\Entity\User;
use App\Tools\NewPDO;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class FriendController extends HelperController
{
    #[Route('/api/friend/request', methods: ['POST'])]
    #[OA\Post(
        path: '/api/friend/request',
        summary: 'Send a friend request',
        description: 'Send a friend request to another user',
        tags: ['Friends'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    'friend_id' => new OA\Property(property: 'friend_id', type: 'integer', description: 'ID of the user to send friend request to', example: 123)
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Friend request sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend request sent')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Missing parameters or request already sent',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Missing parameters')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Friend not found',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend not found')
                    ]
                )
            )
        ]
    )]
    public function requestFriend(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['friend_id'])) {
            return $this->res("Missing parameters", null, 400);
        }

        $user = $this->getUser();
        $friend_found = $this->entityManager->getRepository(User::class)->find($payload['friend_id']);
        if (!$friend_found) {
            return $this->res("Friend not found", null, 404);
        }

        $pdo = new NewPDO();
        $friend_exist = $pdo->fetch("SELECT * 
                                     FROM friend 
                                     WHERE (receiver_id = ? OR applicant_id = ?) AND (receiver_id = ? OR applicant_id = ?)", [$user->getId(), $user->getId(), $friend_found->getId(), $friend_found->getId()]);
        if ($friend_exist) {
            return $this->res("Friend request already sent", null, 400);
        }

        $friend = new Friend();
        $friend->setApplicant($user);
        $friend->setReceiver($friend_found);
        $friend->setWaiting(true);

        $this->entityManager->persist($friend);
        $this->entityManager->flush();

        return $this->res("Friend request sent");
    }

    #[Route('/api/friend/accept', methods: ['POST'])]
    #[OA\Post(
        path: '/api/friend/accept',
        summary: 'Accept a friend request',
        description: 'Accept a pending friend request',
        tags: ['Friends'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    'request_id' => new OA\Property(property: 'request_id', type: 'integer', description: 'ID of the friend request to accept', example: 456)
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Friend request accepted successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend request accepted')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Missing parameters or unauthorized',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Missing parameters')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Friend request not found',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend request not found')
                    ]
                )
            )
        ]
    )]
    public function acceptFriend(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['request_id'])) {
            return $this->res("Missing parameters", null, 400);
        }

        $user = $this->getUser();
        $friend = $this->entityManager->getRepository(Friend::class)->find($payload['request_id']);
        if (!$friend) {
            return $this->res("Friend request not found", null, 404);
        }

        if ($friend->getReceiver() !== $user) {
            return $this->res("You can't accept this friend request", null, 400);
        }

        $friend->setWaiting(false);
        $this->entityManager->persist($friend);
        $this->entityManager->flush();

        return $this->res("Friend request accepted");
    }

    #[Route('/api/friend/decline', methods: ['POST'])]
    #[OA\Post(
        path: '/api/friend/decline',
        summary: 'Decline a friend request',
        description: 'Decline a pending friend request',
        tags: ['Friends'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    'request_id' => new OA\Property(property: 'request_id', type: 'integer', description: 'ID of the friend request to decline', example: 456)
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Friend request declined successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend request declined')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Missing parameters or unauthorized',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Missing parameters')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Friend request not found',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend request not found')
                    ]
                )
            )
        ]
    )]
    public function declineFriend(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['request_id'])) {
            return $this->res("Missing parameters", null, 400);
        }

        $user = $this->getUser();
        $friend = $this->entityManager->getRepository(Friend::class)->find($payload['request_id']);
        if (!$friend) {
            return $this->res("Friend request not found", null, 404);
        }

        if ($friend->getReceiver() !== $user) {
            return $this->res("You can't decline this friend request", null, 400);
        }

        $this->entityManager->remove($friend);
        $this->entityManager->flush();

        return $this->res("Friend request declined");
    }

    #[Route('/api/friend/remove', methods: ['POST'])]
    #[OA\Post(
        path: '/api/friend/remove',
        summary: 'Remove a friend',
        description: 'Remove an existing friend from your friend list',
        tags: ['Friends'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    'friend_id' => new OA\Property(property: 'friend_id', type: 'integer', description: 'ID of the friend to remove', example: 123)
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Friend removed successfully',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend removed')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Missing parameters',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Missing parameters')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Friend not found',
                content: new OA\JsonContent(
                    properties: [
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Friend not found')
                    ]
                )
            )
        ]
    )]
    public function removeFriend(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['friend_id'])) {
            return $this->res("Missing parameters", null, 400);
        }

        $user = $this->getUser();
        $friend = $this->entityManager->getRepository(User::class)->find($payload['friend_id']);
        if (!$friend) {
            return $this->res("Friend not found", null, 404);
        }

        $pdo = new NewPDO();
        $friend = $pdo->fetch("SELECT * 
                                     FROM friend 
                                     WHERE (applicant_id = ? OR receiver_id = ?) AND (applicant_id = ? OR receiver_id = ?)", [$user->getId(), $user->getId(), $friend->getId(), $friend->getId()]);
        if (empty($friend)) {
            return $this->res("Friend not found", null, 404);
        }

        $pdo->exec("DELETE FROM friend WHERE id = ?", [$friend[0]["id"]]);

        return $this->res("Friend removed");
    }

    #[Route('/api/friend/list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/friend/list',
        summary: 'Get list of friends',
        description: 'Get the current user\'s friend list',
        tags: ['Friends'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of friends retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            'id' => new OA\Property(property: 'id', type: 'integer', description: 'Friend user ID', example: 123),
                            'first_name' => new OA\Property(property: 'first_name', type: 'string', description: 'First name', example: 'John'),
                            'last_name' => new OA\Property(property: 'last_name', type: 'string', description: 'Last name', example: 'Doe'),
                            'username' => new OA\Property(property: 'username', type: 'string', description: 'Username/alias', example: 'johndoe')
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function listFriend()
    {
        $user = $this->getUser();
        $pdo = new NewPDO();
        $friends = $pdo->fetch("SELECT * 
                                     FROM friend
                                     WHERE (receiver_id = ? OR applicant_id = ?) AND is_waiting = 0", [$user->getId(), $user->getId()]);

        $user_ids = [];
        foreach ($friends as $friend) {
            if ($friend['receiver_id'] == $user->getId()) {
                $user_ids[] = $friend['applicant_id'];
            } else {
                $user_ids[] = $friend['receiver_id'];
            }
        }

        $users = $pdo->fetch("SELECT id, first_name, last_name, alias AS username 
                                    FROM user 
                                    WHERE id IN " . $pdo->pQMS(sizeof($user_ids)), $user_ids);


        return $this->res($users);
    }

    #[Route('/api/friend/list/request', methods: ['GET'])]
    #[OA\Get(
        path: '/api/friend/list/request',
        summary: 'Get pending friend requests',
        description: 'Get list of pending friend requests received by the current user',
        tags: ['Friends'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of pending friend requests retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            'request_id' => new OA\Property(property: 'request_id', type: 'integer', description: 'Friend request ID', example: 456),
                            'user_id' => new OA\Property(property: 'user_id', type: 'integer', description: 'Requesting user ID', example: 123),
                            'first_name' => new OA\Property(property: 'first_name', type: 'string', description: 'First name', example: 'John'),
                            'last_name' => new OA\Property(property: 'last_name', type: 'string', description: 'Last name', example: 'Doe'),
                            'username' => new OA\Property(property: 'username', type: 'string', description: 'Username/alias', example: 'johndoe')
                        ],
                        type: 'object'
                    )
                )
            )
        ]
    )]
    public function listFriendRequest()
    {
        $user = $this->getUser();
        $pdo = new NewPDO();
        $friends = $pdo->fetch("SELECT friend.id AS request_id, user.id AS user_id, user.first_name, user.last_name, user.alias AS username
                                     FROM friend 
                                     JOIN user ON user.id = friend.applicant_id
                                     WHERE receiver_id = ? AND is_waiting = 1", [$user->getId()]);

        return $this->res($friends);
    }

    #[Route('/api/friend/notification/block/{friend}', methods: ['POST'])]
    #[OA\Post(
        path: '/api/friend/notification/block/{friend}',
        summary: 'Toggle friend notification blocking',
        description: 'Block or unblock notifications from a specific friend',
        tags: ['Friends', 'Notifications'],
        parameters: [
            new OA\Parameter(
                name: 'friend',
                description: 'Friend user ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification settings updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        'user' => new OA\Property(property: 'user', type: 'object', description: 'User object'),
                        'friend' => new OA\Property(property: 'friend', type: 'object', description: 'Friend object'),
                        'notificationBlock' => new OA\Property(property: 'notificationBlock', type: 'boolean', description: 'Notification block status', example: true)
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(property: 'error', type: 'string', example: 'User not found')
                    ]
                )
            )
        ]
    )]
    public function blockNotification(User $friend)
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $notification_settings_repo = $this->entityManager->getRepository(FriendNotificationSettings::class);
        $notification_settings = $notification_settings_repo->findOneBy([
            'user' => $user,
            'friend' => $friend
        ]);

        if (!$notification_settings) {
            $notification_settings = new FriendNotificationSettings();
            $notification_settings
                ->setUser($user)
                ->setFriend($friend)
                ->setNotificationBlock(true);
        } else {
            $notification_settings->setNotificationBlock(!$notification_settings->getNotificationBlock());
        }


        $this->entityManager->persist($notification_settings);
        $this->entityManager->flush();

        return $this->res($notification_settings, ["readData"]);
    }

    #[Route('/api/friend/notification/settings', methods: ['GET'])]
    #[OA\Get(
        path: '/api/friend/notification/settings',
        summary: 'Get notification settings',
        description: 'Get current user\'s friend notification settings',
        tags: ['Friends', 'Notifications'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notification settings retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            'user' => new OA\Property(property: 'user', type: 'object', description: 'User object'),
                            'friend' => new OA\Property(property: 'friend', type: 'object', description: 'Friend object'),
                            'notificationBlock' => new OA\Property(property: 'notificationBlock', type: 'boolean', description: 'Notification block status', example: false)
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User or notification settings not found',
                content: new OA\JsonContent(
                    properties: [
                        'error' => new OA\Property(property: 'error', type: 'string', example: 'No notification settings found')
                    ]
                )
            )
        ]
    )]
    public function getNotificationSettings()
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $notification_settings_repo = $this->entityManager->getRepository(FriendNotificationSettings::class);
        $notification_settings = $notification_settings_repo->findBy(['friend' => $user]);
        if (!$notification_settings) {
            return new JsonResponse(['error' => 'No notification settings found'], 404);
        }

        return $this->res($notification_settings, ["readData"]);
    }

}
