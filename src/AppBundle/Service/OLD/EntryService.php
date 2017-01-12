<?php
namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\Configuration;
use JSend\JSendResponse;

class EntryService extends ScrudService
{
    protected $entityClass;
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $this->configuration = Configuration::create();
            $this->configuration->setMethods(
                ['statistics', 'search', 'create', 'read', 'update', 'delete']
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

    public function statistics(array $request)
    {
        $response = $this->getRepository()->statistics(
            $request,
            $this->getUser(),
            $message
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    public function search(array $request)
    {
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

    public function write(array $request)
    {
        $response = $this->getRepository()->update(
            new Request($request),
            $this->getUser()
        );
        if ($response->hasMessages()) {
            return JSendResponse::fail($response->getMessages())->asArray();
        }
        return JSendResponse::success($response->getData())->asArray();
    }

    public function delete(array $request)
    {
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
