<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\User;
use App\Tools\NewPDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class FriendController extends HelperController
{
    #[Route('/api/friend/request', methods: ['POST'])]
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
                                     WHERE (user_a = ? OR user_b = ?) AND (user_a = ? OR user_b = ?)", [$user->getId(), $user->getId(), $friend->getId(), $friend->getId()]);
        if ($friend_exist) {
            return $this->res("Friend request already sent", null, 400);
        }

        $friend = new Friend();
        $friend->setUserA($user);
        $friend->setUserB($friend_found);
        $friend->setWaiting(true);

        $this->entityManager->persist($friend);
        $this->entityManager->flush();

        return $this->res("Friend request sent");
    }

    #[Route('/api/friend/accept', methods: ['POST'])]
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

        if ($friend->getUserB() !== $user && $friend->getUserA() !== $user) {
            return $this->res("You can't accept this friend request", null, 400);
        }

        $friend->setWaiting(false);
        $this->entityManager->persist($friend);
        $this->entityManager->flush();

        return $this->res("Friend request accepted");
    }

    #[Route('/api/friend/decline', methods: ['POST'])]
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

        if ($friend->getUserB() !== $user && $friend->getUserA() !== $user) {
            return $this->res("You can't decline this friend request", null, 400);
        }

        $this->entityManager->remove($friend);
        $this->entityManager->flush();

        return $this->res("Friend request declined");
    }

    #[Route('/api/friend/remove', methods: ['POST'])]
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
                                     WHERE (user_a = ? OR user_b = ?) AND (user_a = ? OR user_b = ?)", [$user->getId(), $user->getId(), $friend->getId(), $friend->getId()]);
        if (empty($friend)) {
            return $this->res("Friend not found", null, 404);
        }

        $pdo->exec("DELETE FROM friend WHERE id = ?", [$friend[0]["id"]]);

        return $this->res("Friend removed");
    }

    #[Route('/api/friend/list', methods: ['GET'])]
    public function listFriend()
    {
        $user = $this->getUser();
        $pdo = new NewPDO();
        $friends = $pdo->fetch("SELECT * 
                                     FROM friend 
                                     WHERE (user_a = ? OR user_b = ?) AND is_waiting = 0", [$user->getId(), $user->getId()]);

        return $this->res("Friends found", $friends);
    }

    #[Route('/api/friend/list/request', methods: ['GET'])]
    public function listFriendRequest()
    {
        $user = $this->getUser();
        $pdo = new NewPDO();
        $friends = $pdo->fetch("SELECT * 
                                     FROM friend 
                                     WHERE (user_a = ? OR user_b = ?) AND is_waiting = 1", [$user->getId(), $user->getId()]);

        return $this->res("Friend requests found", $friends);
    }

}
