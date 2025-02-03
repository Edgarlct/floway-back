<?php

namespace App\Controller;

use App\Tools\NewPDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RunController extends HelperController
{
    #[Route('/api/run', methods: ["POST"])]
    public function createRun(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['audio_params', 'title', 'time_objective', 'distance_objective', 'price'])) {
            return $this->res("Missing key in payload", null, 400);
        }

        if (!key_exists("time_objective", $payload)) $payload["time_objective"] = null;
        if (!key_exists("distance_objective", $payload)) $payload["distance_objective"] = null;
        if (!key_exists("price", $payload)) $payload["price"] = null;
        if (!key_exists("description", $payload)) $payload["description"] = null;


        $pdo = new NewPDO();
        $pdo->connection->beginTransaction();
        try {
            $next_available_run_id = $pdo->fetch("SELECT * FROM run ORDER BY id DESC LIMIT 1");
            if (empty($next_available_run_id)) $next_available_run_id = 1;
            else $next_available_run_id = $next_available_run_id[0]['id'] + 1;

            $audio_ids = $pdo->extractProperty("audio_id", $payload['audio_params']);
            $audios = $pdo->fetch("SELECT * FROM audio WHERE id IN " . $pdo->pQMS(sizeof($audio_ids)), $audio_ids);
            $indexed_audios = $pdo->indexArray($audios, 'id');

            $activation_params = [];
            foreach ($payload["audio_params"] as $audio_param) {
                if (!isset($indexed_audios[$audio_param['audio_id']])) continue;

                $activation_params[] = $next_available_run_id;                                                          // run_id
                $activation_params[] = $audio_param['audio_id'];                                                        // audio_id
                $activation_params[] = $audio_param['time'] !== null ? $audio_param['time'] : null;                     // time
                $activation_params[] = $audio_param['distance'] !== null ? $audio_param['distance'] : null;             // distance
            }

            $run_params = [
                $next_available_run_id,                                 // id
                $payload['title'],                                      // title
                $payload['time_objective'],                             // time_objective
                $payload['distance_objective'],                         // distance_objective
                $payload['price'] !== null ? $payload['price'] : null,  // price
                $payload['price'] !== null ? 1 : 0,                     // is_buyable
                $payload['description'],                                // description
                $this->getUser()->getId()                               // user_id
            ];

            $pdo->exec("INSERT INTO run (id, title, time_objective, distance_objective, price, is_buyable, description, user_id) VALUES " . $pdo->pQMS(8), $run_params);
            $pdo->exec("INSERT INTO activation_param (run_id, audio_id, time, distance) VALUES " . $pdo->aNPQMS($activation_params, 4), $activation_params);

            $pdo->connection->commit();
            return $this->res(["run_id" => $next_available_run_id], null, 201);
        } catch (\Throwable $th) {
            $pdo->connection->rollBack();
            return $this->res($th->getMessage(), 500);
        }
    }

    #[Route('/api/run', methods: ["GET"])]
    public function getAvailableRuns()
    {
        $pdo = new NewPDO();
        $bought_runs = $pdo->fetch("SELECT * FROM run_buyer WHERE user_id = ?", [$this->getUser()->getId()]);
        $run_ids = $pdo->extractProperty("run_id", $bought_runs);

        $sql = "SELECT * FROM run WHERE user_id = ? ";
        if (!empty($run_ids)) $sql .= "OR id IN " . $pdo->pQMS(sizeof($run_ids));
        $runs_result = $pdo->fetch($sql, array_merge([$this->getUser()->getId()], $run_ids));

        $runs = [];
        foreach ($runs_result as $run) {
            $bought = in_array($run['id'], $run_ids);
            $run['is_bought'] = $bought;
            if (!$bought && $run["is_deleted"]) continue;

            $runs[] = $run;
        }

        $user_ids = $pdo->extractProperty("user_id", $runs);
        $run_ids = $pdo->extractProperty("id", $runs);

        if (empty($user_ids)) return $this->success(["runs" => $runs]);
        if (empty($run_ids)) return $this->success(["runs" => $runs, "users" => []]);

        $activation_param = $pdo->fetch("SELECT * FROM activation_param WHERE run_id IN " . $pdo->pQMS(sizeof($run_ids)), $run_ids);
        $users = $pdo->fetch("SELECT id,first_name, last_name, picture_path FROM user WHERE id IN " . $pdo->pQMS(sizeof($user_ids)), $user_ids);

        return $this->success([
            "runs" => $runs,
            "activation_param" => $activation_param,
            "users" => $users
        ]);
    }

    #[Route('/api/run', methods: ["PUT"])]
    public function updateRun(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['run_id', 'title', 'time_objective', 'distance_objective', 'price'])) {
            return $this->res("Missing key in payload", null, 400);
        }

        $pdo = new NewPDO();
        $pdo->connection->beginTransaction();
        try {
            $run = $pdo->fetch("SELECT * FROM run WHERE id = ?", [$payload['run_id']]);
            if (empty($run)) return $this->res("Run not found", null, 404);
            if ($run[0]["is_buyable"]) return $this->res("Run is buyable, you can't update it", null, 400);

            $run_params = [
                $payload['run_id'],                                    // id
                $payload['title'],                                     // title
                $payload['time_objective'],                            // time_objective
                $payload['distance_objective'],                        // distance_objective
                $payload['price'] !== null ? $payload['price'] : null, // price
                $payload['price'] !== null ? 1 : 0,                    // is_buyable
            ];

            $pdo->exec("UPDATE run SET title = ?, time_objective = ?, distance_objective = ?, price = ?, is_buyable = ? WHERE id = ?", $run_params);
            $pdo->connection->commit();

            return $this->res("Run updated successfully");
        } catch (\Throwable $th) {
            $pdo->connection->rollBack();
            return $this->res($th->getMessage(), 500);
        }
    }


    // get one run by id
    #[Route('/api/run/{id}', methods: ["GET"])]
    public function getRun($id)
    {
        $pdo = new NewPDO();
        $run = $pdo->fetch("SELECT * FROM run WHERE id = ?", [$id]);
        if (empty($run)) return $this->res("Run not found", null, 404);

        $activation_param = $pdo->fetch("SELECT * FROM activation_param WHERE run_id = ?", [$id]);
        $user = $pdo->fetch("SELECT id,first_name, last_name, picture_path FROM user WHERE id = ?", [$run[0]['user_id']]);

        return $this->success([
            "run" => $run[0],
            "activation_param" => $activation_param,
            "user" => empty($user) ? null : $user[0]
        ]);
    }

    // delete run
    #[Route('/api/run/{id}', methods: ["DELETE"])]
    public function deleteRun($id)
    {
        $pdo = new NewPDO();
        $pdo->connection->beginTransaction();
        try {
            $run = $pdo->fetch("SELECT * FROM run WHERE id = ?", [$id]);
            if (empty($run)) return $this->res("Run not found", null, 404);
            if ($run[0]["is_buyable"]) return $this->res("Run is buyable, you can't delete it", null, 400);

            $pdo->exec("UPDATE run SET is_deleted = 1 WHERE id = ?", [$id]);
            $pdo->connection->commit();

            return $this->res("Run deleted successfully");
        } catch (\Throwable $th) {
            $pdo->connection->rollBack();
            return $this->res($th->getMessage(), 500);
        }
    }
}
