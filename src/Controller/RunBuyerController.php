<?php

namespace App\Controller;

use App\Entity\Run;
use App\Entity\RunBuyer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RunBuyerController extends HelperController
{
    #[Route('/api/run/buy', name: 'buy_run', methods: ['POST'])]
    public function buyRun(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['run_id'])) {
            return $this->res("Missing parameters", null, 400);
        }

        $user = $this->getUser();
        $run = $this->entityManager->getRepository(Run::class)->find($payload['run_id']);
        if (!$run) {
            return $this->res("Run not found", null, 404);
        }

        $alreadyBought = $this->entityManager->getRepository(Run::class)->findOneBy(['user' => $user, 'run' => $run]);
        if ($alreadyBought) {
            return $this->res("You already bought this run", null, 400);
        }

        $run_buyer = new RunBuyer();
        $run_buyer->setUser($user);
        $run_buyer->setRun($run);

        $this->entityManager->persist($run_buyer);
        $this->entityManager->flush();

        return $this->res("Run bought");
    }
}
