<?php

namespace App\Controller;

use App\Entity\ActivationParam;
use App\Tools\NewPDO;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivationParamController extends HelperController
{
//    #[Route('/api/activation/param', methods: ["DELETE"])]
//    #[OA\Delete(
//        path: '/api/activation/param',
//        summary: 'Delete an activation parameter',
//        description: 'Delete an activation parameter from a non-buyable run',
//        tags: ['Activation Parameters'],
//        security: [['bearerAuth' => []]],
//        parameters: [
//            new OA\Parameter(
//                name: 'activation_param_id',
//                description: 'Activation parameter ID to delete',
//                in: 'query',
//                required: true,
//                schema: new OA\Schema(type: 'integer')
//            )
//        ],
//        responses: [
//            new OA\Response(
//                response: 204,
//                description: 'Activation parameter deleted successfully'
//            ),
//            new OA\Response(
//                response: 404,
//                description: 'Activation parameter not found or run is buyable',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Activation param not found or run is buyable')
//                    ]
//                )
//            )
//        ]
//    )]
//    public function deleteActivationParam(Request $request)
//    {
//        $activation_param_id = $request->get('activation_param_id');
//        $pdo = new NewPDO();
//        $param = $pdo->fetch("SELECT id FROM activation_param
//                                    JOIN run ON run.id = activation_param.run_id
//                                    WHERE activation_param.id = ? AND run.is_buyable IS NOT TRUE", [$activation_param_id]);
//
//        if (empty($param)) {
//            return $this->res("Activation param not found or run is buyable", null, 404);
//        }
//
//        $pdo->exec("DELETE FROM activation_param WHERE id = ?", [$activation_param_id]);
//        return $this->res(null, null, 204);
//    }
//
//    #[Route('/api/activation/param', methods: ["PUT"])]
//    #[OA\Put(
//        path: '/api/activation/param',
//        summary: 'Update an activation parameter',
//        description: 'Update time or distance for an activation parameter',
//        tags: ['Activation Parameters'],
//        security: [['bearerAuth' => []]],
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(
//                properties: [
//                    'activation_param_id' => new OA\Property(property: 'activation_param_id', type: 'integer', description: 'Activation parameter ID', example: 1),
//                    'time' => new OA\Property(property: 'time', type: 'integer', description: 'Activation time in seconds', example: 300),
//                    'distance' => new OA\Property(property: 'distance', type: 'number', format: 'float', description: 'Activation distance in meters', example: 1000.0)
//                ],
//                type: 'object'
//            )
//        ),
//        responses: [
//            new OA\Response(
//                response: 204,
//                description: 'Activation parameter updated successfully'
//            ),
//            new OA\Response(
//                response: 400,
//                description: 'Bad request - Missing parameters',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Missing key in payload')
//                    ]
//                )
//            ),
//            new OA\Response(
//                response: 404,
//                description: 'Activation parameter not found',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Activation param not found')
//                    ]
//                )
//            )
//        ]
//    )]
//    public function updateActivationParam(Request $request)
//    {
//        $payload = json_decode($request->getContent(), true);
//        if (!$this->checkKeyInPayload($payload, ['activation_param_id'])) {
//            return $this->res("Missing key in payload", null, 400);
//        }
//
//        $activation_param = $this->entityManager->getRepository(ActivationParam::class)->find($payload['activation_param_id']);
//        if (empty($activation_param)) {
//            return $this->res("Activation param not found", null, 404);
//        }
//
//        if (isset($payload['time'])) {
//            $activation_param->setTime($payload['time']);
//        }
//
//        if (isset($payload['distance'])) {
//            $activation_param->setDistance($payload['distance']);
//        }
//
//        $this->entityManager->flush();
//        $this->entityManager->persist($activation_param);
//        return $this->res(null, null, 204);
//    }
}
