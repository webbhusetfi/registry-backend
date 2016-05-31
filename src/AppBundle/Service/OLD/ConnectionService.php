<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\ScrudConfiguration;
use JSend\JSendResponse;

class ConnectionService extends ScrudService
{
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $methods = ['search', 'create', 'read', 'update', 'delete'];
            $constraints = [];

            $this->configuration = ScrudConfiguration::create(
                    $this->getDoctrine(),
                    'AppBundle\Entity\Connection',
                    $methods
                )
                ->setConstraints($constraints)
                ;
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
