<?php
namespace AppBundle\Service\Common;

use AppBundle\Service\Common\Service;
use AppBundle\Service\Configuration\Configuration;
use JSend\JSendResponse;

class JSendService extends Service
{
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $this->configuration = new Configuration();
            $this->configuration->setMethods(
                ['search', 'create', 'read', 'update', 'delete']
            );
        }
        return $this->configuration;
    }

    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function getRepository()
    {
        return $this->getDoctrine()->getRepository($this->entityClass);
    }

    public function search(array $request)
    {
        $message = [];
        $response = $this->getRepository()->search(
            $request,
            $this->getUser(),
            $message
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    public function create(array $request)
    {
        $message = [];
        $response = $this->getRepository()->create(
            $request,
            $this->getUser(),
            $message
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    public function read(array $request)
    {
        $message = [];
        $response = $this->getRepository()->read(
            $request,
            $this->getUser(),
            $message
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    public function update(array $request)
    {
        $message = [];
        $response = $this->getRepository()->update(
            $request,
            $this->getUser(),
            $message
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    public function delete(array $request)
    {
        $message = [];
        $response = $this->getRepository()->delete(
            $request,
            $this->getUser(),
            $message
        );
        if ($response) {
            return JSendResponse::success()->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }
}
