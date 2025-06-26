<?php

namespace App\Controller;

use App\Tools\NewPDO;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RunController extends HelperController
{
//    #[Route('/api/run', methods: ["POST"])]
//    #[OA\Post(
//        path: '/api/run',
//        summary: 'Create a new run',
//        description: 'Create a new run with audio parameters and objectives',
//        tags: ['Runs'],
//        security: [['bearerAuth' => []]],
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(
//                properties: [
//                    'title' => new OA\Property(property: 'title', type: 'string', description: 'Run title', example: 'Morning Jog'),
//                    'time_objective' => new OA\Property(property: 'time_objective', type: 'integer', description: 'Time objective in seconds', example: 1800),
//                    'distance_objective' => new OA\Property(property: 'distance_objective', type: 'number', format: 'float', description: 'Distance objective in meters', example: 5000.0),
//                    'price' => new OA\Property(property: 'price', type: 'number', format: 'float', description: 'Price if run is buyable', example: 9.99),
//                    'description' => new OA\Property(property: 'description', type: 'string', description: 'Run description', example: 'A relaxing morning run'),
//                    'audio_params' => new OA\Property(
//                        property: 'audio_params',
//                        type: 'array',
//                        description: 'Audio activation parameters',
//                        items: new OA\Items(
//                            properties: [
//                                'audio_id' => new OA\Property(property: 'audio_id', type: 'integer', description: 'Audio file ID', example: 1),
//                                'time' => new OA\Property(property: 'time', type: 'integer', description: 'Activation time in seconds', example: 300),
//                                'distance' => new OA\Property(property: 'distance', type: 'number', format: 'float', description: 'Activation distance in meters', example: 1000.0)
//                            ],
//                            type: 'object'
//                        )
//                    )
//                ],
//                type: 'object'
//            )
//        ),
//        responses: [
//            new OA\Response(
//                response: 201,
//                description: 'Run created successfully',
//                content: new OA\JsonContent(
//                    properties: [
//                        'run_id' => new OA\Property(property: 'run_id', type: 'integer', description: 'Created run ID', example: 123)
//                    ],
//                    type: 'object'
//                )
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
//                response: 500,
//                description: 'Internal server error'
//            )
//        ]
//    )]
//    public function createRun(Request $request)
//    {
//        $payload = json_decode($request->getContent(), true);
//        if (!$this->checkKeyInPayload($payload, ['audio_params', 'title', 'time_objective', 'distance_objective', 'price'])) {
//            return $this->res("Missing key in payload", null, 400);
//        }
//
//        if (!key_exists("time_objective", $payload)) $payload["time_objective"] = null;
//        if (!key_exists("distance_objective", $payload)) $payload["distance_objective"] = null;
//        if (!key_exists("price", $payload)) $payload["price"] = null;
//        if (!key_exists("description", $payload)) $payload["description"] = null;
//
//
//        $pdo = new NewPDO();
//        $pdo->connection->beginTransaction();
//        try {
//            $next_available_run_id = $pdo->fetch("SELECT * FROM run ORDER BY id DESC LIMIT 1");
//            if (empty($next_available_run_id)) $next_available_run_id = 1;
//            else $next_available_run_id = $next_available_run_id[0]['id'] + 1;
//
//            $audio_ids = $pdo->extractProperty("audio_id", $payload['audio_params']);
//            $audios = $pdo->fetch("SELECT * FROM audio WHERE id IN " . $pdo->pQMS(sizeof($audio_ids)), $audio_ids);
//            $indexed_audios = $pdo->indexArray($audios, 'id');
//
//            $activation_params = [];
//            foreach ($payload["audio_params"] as $audio_param) {
//                if (!isset($indexed_audios[$audio_param['audio_id']])) continue;
//
//                $activation_params[] = $next_available_run_id;                                                          // run_id
//                $activation_params[] = $audio_param['audio_id'];                                                        // audio_id
//                $activation_params[] = $audio_param['time'] !== null ? $audio_param['time'] : null;                     // time
//                $activation_params[] = $audio_param['distance'] !== null ? $audio_param['distance'] : null;             // distance
//            }
//
//            $run_params = [
//                $next_available_run_id,                                 // id
//                $payload['title'],                                      // title
//                $payload['time_objective'],                             // time_objective
//                $payload['distance_objective'],                         // distance_objective
//                $payload['price'] !== null ? $payload['price'] : null,  // price
//                $payload['price'] !== null ? 1 : 0,                     // is_buyable
//                $payload['description'],                                // description
//                $this->getUser()->getId()                               // user_id
//            ];
//
//            $pdo->exec("INSERT INTO run (id, title, time_objective, distance_objective, price, is_buyable, description, user_id) VALUES " . $pdo->pQMS(8), $run_params);
//            $pdo->exec("INSERT INTO activation_param (run_id, audio_id, time, distance) VALUES " . $pdo->aNPQMS($activation_params, 4), $activation_params);
//
//            $pdo->connection->commit();
//            return $this->res(["run_id" => $next_available_run_id], null, 201);
//        } catch (\Throwable $th) {
//            $pdo->connection->rollBack();
//            return $this->res($th->getMessage(), 500);
//        }
//    }
//
//    #[Route('/api/run', methods: ["GET"])]
//    #[OA\Get(
//        path: '/api/run',
//        summary: 'Get available runs',
//        description: 'Get list of available runs for the current user (owned and purchased)',
//        tags: ['Runs'],
//        security: [['bearerAuth' => []]],
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: 'Available runs retrieved successfully',
//                content: new OA\JsonContent(
//                    properties: [
//                        'runs' => new OA\Property(
//                            property: 'runs',
//                            type: 'array',
//                            items: new OA\Items(
//                                properties: [
//                                    'id' => new OA\Property(property: 'id', type: 'integer', example: 1),
//                                    'title' => new OA\Property(property: 'title', type: 'string', example: 'Morning Jog'),
//                                    'time_objective' => new OA\Property(property: 'time_objective', type: 'integer', example: 1800),
//                                    'distance_objective' => new OA\Property(property: 'distance_objective', type: 'number', example: 5000.0),
//                                    'price' => new OA\Property(property: 'price', type: 'number', example: 9.99),
//                                    'is_buyable' => new OA\Property(property: 'is_buyable', type: 'boolean', example: true),
//                                    'is_bought' => new OA\Property(property: 'is_bought', type: 'boolean', example: false),
//                                    'user_id' => new OA\Property(property: 'user_id', type: 'integer', example: 123)
//                                ],
//                                type: 'object'
//                            )
//                        ),
//                        'activation_param' => new OA\Property(
//                            property: 'activation_param',
//                            type: 'array',
//                            items: new OA\Items(
//                                properties: [
//                                    'run_id' => new OA\Property(property: 'run_id', type: 'integer', example: 1),
//                                    'audio_id' => new OA\Property(property: 'audio_id', type: 'integer', example: 1),
//                                    'time' => new OA\Property(property: 'time', type: 'integer', example: 300),
//                                    'distance' => new OA\Property(property: 'distance', type: 'number', example: 1000.0)
//                                ],
//                                type: 'object'
//                            )
//                        ),
//                        'users' => new OA\Property(
//                            property: 'users',
//                            type: 'array',
//                            items: new OA\Items(
//                                properties: [
//                                    'id' => new OA\Property(property: 'id', type: 'integer', example: 123),
//                                    'first_name' => new OA\Property(property: 'first_name', type: 'string', example: 'John'),
//                                    'last_name' => new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
//                                    'picture_path' => new OA\Property(property: 'picture_path', type: 'string', example: '/uploads/profile.jpg')
//                                ],
//                                type: 'object'
//                            )
//                        )
//                    ],
//                    type: 'object'
//                )
//            )
//        ]
//    )]
//    public function getAvailableRuns()
//    {
//        $pdo = new NewPDO();
//        $bought_runs = $pdo->fetch("SELECT * FROM run_buyer WHERE user_id = ?", [$this->getUser()->getId()]);
//        $run_ids = $pdo->extractProperty("run_id", $bought_runs);
//
//        $sql = "SELECT * FROM run WHERE user_id = ? ";
//        if (!empty($run_ids)) $sql .= "OR id IN " . $pdo->pQMS(sizeof($run_ids));
//        $runs_result = $pdo->fetch($sql, array_merge([$this->getUser()->getId()], $run_ids));
//
//        $runs = [];
//        foreach ($runs_result as $run) {
//            $bought = in_array($run['id'], $run_ids);
//            $run['is_bought'] = $bought;
//            if (!$bought && $run["is_deleted"]) continue;
//
//            $runs[] = $run;
//        }
//
//        $user_ids = $pdo->extractProperty("user_id", $runs);
//        $run_ids = $pdo->extractProperty("id", $runs);
//
//        if (empty($user_ids)) return $this->success(["runs" => $runs]);
//        if (empty($run_ids)) return $this->success(["runs" => $runs, "users" => []]);
//
//        $activation_param = $pdo->fetch("SELECT * FROM activation_param WHERE run_id IN " . $pdo->pQMS(sizeof($run_ids)), $run_ids);
//        $users = $pdo->fetch("SELECT id,first_name, last_name, picture_path FROM user WHERE id IN " . $pdo->pQMS(sizeof($user_ids)), $user_ids);
//
//        return $this->success([
//            "runs" => $runs,
//            "activation_param" => $activation_param,
//            "users" => $users
//        ]);
//    }
//
//    #[Route('/api/run', methods: ["PUT"])]
//    #[OA\Put(
//        path: '/api/run',
//        summary: 'Update a run',
//        description: 'Update an existing run (only non-buyable runs can be updated)',
//        tags: ['Runs'],
//        security: [['bearerAuth' => []]],
//        requestBody: new OA\RequestBody(
//            required: true,
//            content: new OA\JsonContent(
//                properties: [
//                    'run_id' => new OA\Property(property: 'run_id', type: 'integer', description: 'Run ID to update', example: 123),
//                    'title' => new OA\Property(property: 'title', type: 'string', description: 'Run title', example: 'Updated Morning Jog'),
//                    'time_objective' => new OA\Property(property: 'time_objective', type: 'integer', description: 'Time objective in seconds', example: 2000),
//                    'distance_objective' => new OA\Property(property: 'distance_objective', type: 'number', format: 'float', description: 'Distance objective in meters', example: 6000.0),
//                    'price' => new OA\Property(property: 'price', type: 'number', format: 'float', description: 'Price if run is buyable', example: 12.99)
//                ],
//                type: 'object'
//            )
//        ),
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: 'Run updated successfully',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Run updated successfully')
//                    ]
//                )
//            ),
//            new OA\Response(
//                response: 400,
//                description: 'Bad request - Missing parameters or run is buyable',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Run is buyable, you can\'t update it')
//                    ]
//                )
//            ),
//            new OA\Response(
//                response: 404,
//                description: 'Run not found'
//            ),
//            new OA\Response(
//                response: 500,
//                description: 'Internal server error'
//            )
//        ]
//    )]
//    public function updateRun(Request $request)
//    {
//        $payload = json_decode($request->getContent(), true);
//        if (!$this->checkKeyInPayload($payload, ['run_id', 'title', 'time_objective', 'distance_objective', 'price'])) {
//            return $this->res("Missing key in payload", null, 400);
//        }
//
//        $pdo = new NewPDO();
//        $pdo->connection->beginTransaction();
//        try {
//            $run = $pdo->fetch("SELECT * FROM run WHERE id = ?", [$payload['run_id']]);
//            if (empty($run)) return $this->res("Run not found", null, 404);
//            if ($run[0]["is_buyable"]) return $this->res("Run is buyable, you can't update it", null, 400);
//
//            $run_params = [
//                $payload['run_id'],                                    // id
//                $payload['title'],                                     // title
//                $payload['time_objective'],                            // time_objective
//                $payload['distance_objective'],                        // distance_objective
//                $payload['price'] !== null ? $payload['price'] : null, // price
//                $payload['price'] !== null ? 1 : 0,                    // is_buyable
//            ];
//
//            $pdo->exec("UPDATE run SET title = ?, time_objective = ?, distance_objective = ?, price = ?, is_buyable = ? WHERE id = ?", $run_params);
//            $pdo->connection->commit();
//
//            return $this->res("Run updated successfully");
//        } catch (\Throwable $th) {
//            $pdo->connection->rollBack();
//            return $this->res($th->getMessage(), 500);
//        }
//    }
//
//
//    #[Route('/api/run/{id}', methods: ["GET"])]
//    #[OA\Get(
//        path: '/api/run/{id}',
//        summary: 'Get a specific run',
//        description: 'Get detailed information about a specific run',
//        tags: ['Runs'],
//        security: [['bearerAuth' => []]],
//        parameters: [
//            new OA\Parameter(
//                name: 'id',
//                description: 'Run ID',
//                in: 'path',
//                required: true,
//                schema: new OA\Schema(type: 'integer')
//            )
//        ],
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: 'Run details retrieved successfully',
//                content: new OA\JsonContent(
//                    properties: [
//                        'run' => new OA\Property(
//                            property: 'run',
//                            properties: [
//                                'id' => new OA\Property(property: 'id', type: 'integer', example: 1),
//                                'title' => new OA\Property(property: 'title', type: 'string', example: 'Morning Jog'),
//                                'time_objective' => new OA\Property(property: 'time_objective', type: 'integer', example: 1800),
//                                'distance_objective' => new OA\Property(property: 'distance_objective', type: 'number', example: 5000.0),
//                                'price' => new OA\Property(property: 'price', type: 'number', example: 9.99),
//                                'is_buyable' => new OA\Property(property: 'is_buyable', type: 'boolean', example: true),
//                                'description' => new OA\Property(property: 'description', type: 'string', example: 'A relaxing morning run'),
//                                'user_id' => new OA\Property(property: 'user_id', type: 'integer', example: 123)
//                            ],
//                            type: 'object'
//                        ),
//                        'activation_param' => new OA\Property(
//                            property: 'activation_param',
//                            type: 'array',
//                            items: new OA\Items(
//                                properties: [
//                                    'run_id' => new OA\Property(property: 'run_id', type: 'integer', example: 1),
//                                    'audio_id' => new OA\Property(property: 'audio_id', type: 'integer', example: 1),
//                                    'time' => new OA\Property(property: 'time', type: 'integer', example: 300),
//                                    'distance' => new OA\Property(property: 'distance', type: 'number', example: 1000.0)
//                                ],
//                                type: 'object'
//                            )
//                        ),
//                        'user' => new OA\Property(
//                            property: 'user',
//                            properties: [
//                                'id' => new OA\Property(property: 'id', type: 'integer', example: 123),
//                                'first_name' => new OA\Property(property: 'first_name', type: 'string', example: 'John'),
//                                'last_name' => new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
//                                'picture_path' => new OA\Property(property: 'picture_path', type: 'string', example: '/uploads/profile.jpg')
//                            ],
//                            type: 'object'
//                        )
//                    ],
//                    type: 'object'
//                )
//            ),
//            new OA\Response(
//                response: 404,
//                description: 'Run not found'
//            )
//        ]
//    )]
//    public function getRun($id)
//    {
//        $pdo = new NewPDO();
//        $run = $pdo->fetch("SELECT * FROM run WHERE id = ?", [$id]);
//        if (empty($run)) return $this->res("Run not found", null, 404);
//
//        $activation_param = $pdo->fetch("SELECT * FROM activation_param WHERE run_id = ?", [$id]);
//        $user = $pdo->fetch("SELECT id,first_name, last_name, picture_path FROM user WHERE id = ?", [$run[0]['user_id']]);
//
//        return $this->success([
//            "run" => $run[0],
//            "activation_param" => $activation_param,
//            "user" => empty($user) ? null : $user[0]
//        ]);
//    }
//
//    #[Route('/api/run/{id}', methods: ["DELETE"])]
//    #[OA\Delete(
//        path: '/api/run/{id}',
//        summary: 'Delete a run',
//        description: 'Soft delete a run (only non-buyable runs can be deleted)',
//        tags: ['Runs'],
//        security: [['bearerAuth' => []]],
//        parameters: [
//            new OA\Parameter(
//                name: 'id',
//                description: 'Run ID to delete',
//                in: 'path',
//                required: true,
//                schema: new OA\Schema(type: 'integer')
//            )
//        ],
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: 'Run deleted successfully',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Run deleted successfully')
//                    ]
//                )
//            ),
//            new OA\Response(
//                response: 400,
//                description: 'Bad request - Run is buyable and cannot be deleted',
//                content: new OA\JsonContent(
//                    properties: [
//                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Run is buyable, you can\'t delete it')
//                    ]
//                )
//            ),
//            new OA\Response(
//                response: 404,
//                description: 'Run not found'
//            ),
//            new OA\Response(
//                response: 500,
//                description: 'Internal server error'
//            )
//        ]
//    )]
//    public function deleteRun($id)
//    {
//        $pdo = new NewPDO();
//        $pdo->connection->beginTransaction();
//        try {
//            $run = $pdo->fetch("SELECT * FROM run WHERE id = ?", [$id]);
//            if (empty($run)) return $this->res("Run not found", null, 404);
//            if ($run[0]["is_buyable"]) return $this->res("Run is buyable, you can't delete it", null, 400);
//
//            $pdo->exec("UPDATE run SET is_deleted = 1 WHERE id = ?", [$id]);
//            $pdo->connection->commit();
//
//            return $this->res("Run deleted successfully");
//        } catch (\Throwable $th) {
//            $pdo->connection->rollBack();
//            return $this->res($th->getMessage(), 500);
//        }
//    }
}
