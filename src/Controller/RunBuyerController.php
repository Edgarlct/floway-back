<?php

namespace App\Controller;

use App\Entity\Run;
use App\Entity\RunBuyer;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class RunBuyerController extends HelperController
{
//    #[Route('/api/run/buy', name: 'buy_run', methods: ['POST'])]
//    #[OA\Post(
//        path: '/api/run/buy',
//        summary: 'Purchase a run',
//        description: 'Purchase a buyable run to add it to your available runs',
//        tags: ['Runs', 'Purchase'],
//        security: [['bearerAuth' => []]],
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(
//                properties: [
//                    'run_id' => new OA\Property(property: 'run_id', type: 'integer', description: 'ID of the run to purchase', example: 123)
//                ],
//                type: 'object'
//            )
//        ),
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: 'Run purchased successfully',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Run bought')
//                    ]
//                )
//            ),
//            new OA\Response(
//                response: 400,
//                description: 'Bad request - Missing parameters or already purchased',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'You already bought this run')
//                    ]
//                )
//            ),
//            new OA\Response(
//                response: 404,
//                description: 'Run not found',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Run not found')
//                    ]
//                )
//            )
//        ]
//    )]
//    public function buyRun(Request $request)
//    {
//        $payload = json_decode($request->getContent(), true);
//        if (!$this->checkKeyInPayload($payload, ['run_id'])) {
//            return $this->res("Missing parameters", null, 400);
//        }
//
//        $user = $this->getUser();
//        $run = $this->entityManager->getRepository(Run::class)->find($payload['run_id']);
//        if (!$run) {
//            return $this->res("Run not found", null, 404);
//        }
//
//        $alreadyBought = $this->entityManager->getRepository(Run::class)->findOneBy(['user' => $user, 'run' => $run]);
//        if ($alreadyBought) {
//            return $this->res("You already bought this run", null, 400);
//        }
//
//        $run_buyer = new RunBuyer();
//        $run_buyer->setUser($user);
//        $run_buyer->setRun($run);
//
//        $this->entityManager->persist($run_buyer);
//        $this->entityManager->flush();
//
//        return $this->res("Run bought");
//    }
}
