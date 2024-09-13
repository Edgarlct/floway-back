<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class HelperController extends AbstractController
{
    public $entityManager;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function res($data, $serialise_group = [], $status = 200)
    {
        if (empty($serialise_group)) {
            return new JsonResponse($data, $status);
        }
        return new Response($this->serialize($data, $serialise_group), $status, ["content-type" => "application/json"]);
    }

    public function serialize($data, $serializationGroups = []): string
    {
        $context = SerializationContext::create();
        $context->setSerializeNull(true);
        $context->setGroups($serializationGroups);

        return $this->serializer->serialize($data, 'json',["groups" => "readData"]);
    }

    /**
     * $this->success($items(=null), "toto") // 204 no content
     * $this->success($items(=null), "toto", 200, true) // [] 200
     * @param $data
     * @param array $serializationGroups
     * @param int $code
     * @param false $emptyArrayIfVoid
     * @return JsonResponse|Response
     */
    protected function success($data = '', $serializationGroups = [], $code = Response::HTTP_OK, $emptyArrayIfVoid = true) {

        if(empty($data)) {
            if($emptyArrayIfVoid){
                return new JsonResponse([]);
            }
            return $this->void();
        }

        if(empty($serializationGroups)) {
            return new JsonResponse($data, $code);
        }

        return new Response($this->serialize($data, $serializationGroups), $code, ["content-type" => "application/json"]);

    }

    /**
     * @return Response
     */
    public function void(): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }


    public function checkKeyInPayload($payload, $keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                return false;
            }
        }
        return true;
    }

}
