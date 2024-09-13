<?php

namespace App\Controller;

use App\Entity\ActivationParam;
use App\Tools\NewPDO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivationParamController extends HelperController
{
    #[Route('/api/activation/param', methods: ["DELETE"])]
    public function deleteActivationParam(Request $request)
    {
        $activation_param_id = $request->get('activation_param_id');
        $pdo = new NewPDO();
        $param = $pdo->fetch("SELECT id FROM activation_param 
                                    JOIN run ON run.id = activation_param.run_id 
                                    WHERE activation_param.id = ? AND run.is_buyable IS NOT TRUE", [$activation_param_id]);

        if (empty($param)) {
            return $this->res("Activation param not found or run is buyable", null, 404);
        }

        $pdo->exec("DELETE FROM activation_param WHERE id = ?", [$activation_param_id]);
        return $this->res(null, null, 204);
    }

    #[Route('/api/activation/param', methods: ["PUT"])]
    public function updateActivationParam(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if (!$this->checkKeyInPayload($payload, ['activation_param_id'])) {
            return $this->res("Missing key in payload", null, 400);
        }

        $activation_param = $this->entityManager->getRepository(ActivationParam::class)->find($payload['activation_param_id']);
        if (empty($activation_param)) {
            return $this->res("Activation param not found", null, 404);
        }

        if (isset($payload['time'])) {
            $activation_param->setTime($payload['time']);
        }

        if (isset($payload['distance'])) {
            $activation_param->setDistance($payload['distance']);
        }

        $this->entityManager->flush();
        $this->entityManager->persist($activation_param);
        return $this->res(null, null, 204);
    }
}
