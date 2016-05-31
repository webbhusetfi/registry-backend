<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\JSendService;
use AppBundle\Service\Configuration\Configuration;
use JSend\JSendResponse;

class EntryService extends JSendService
{
    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {
            $this->configuration = new Configuration();
            $this->configuration->setMethods(
                ['statistics', 'search', 'create', 'read', 'update', 'delete']
            );
        }
        return $this->configuration;
    }

    public function statistics(array $request)
    {
        $message = [];
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
}
