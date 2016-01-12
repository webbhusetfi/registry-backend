<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\ScrudService;
use AppBundle\Service\Configuration\ScrudConfiguration;
use AppBundle\Entity\User;
use JSend\JSendResponse;

class EntryService extends ScrudService
{
    protected $entity;
    protected $configuration;

    public function getConfiguration($name = null)
    {
        if (!isset($this->configuration)) {

//             $entryAttrs = ['externalId', 'registry', 'type'];
            $this->configuration[0] = ScrudConfiguration::create(
                $this->getDoctrine(),
                'AppBundle\Entity\Entry',
                ['search', 'create', 'read', 'update', 'delete']
            )
/*            ->setCreateAttributes($entryAttrs)
            ->setUpdateAttributes($entryAttrs)*/;

//             $organizationAttrs = $entryAttrs;
//             $organizationAttrs[] = 'name';
            $this->configuration['ORGANIZATION'] = ScrudConfiguration::create(
                $this->getDoctrine(),
                'AppBundle\Entity\Organization',
                ['search', 'create', 'read', 'update', 'delete']
            )
/*            ->setCreateAttributes($organizationAttrs)
            ->setUpdateAttributes($organizationAttrs)*/;

//             $personAttrs = $entryAttrs;
//             $personAttrs[] = 'gender';
//             $personAttrs[] = 'firstName';
//             $personAttrs[] = 'lastName';
//             $personAttrs[] = 'birthdate';
            $this->configuration['PERSON'] = ScrudConfiguration::create(
                $this->getDoctrine(),
                'AppBundle\Entity\Person',
                ['search', 'create', 'read', 'update', 'delete']
            )
/*            ->setCreateAttributes($personAttrs)
            ->setUpdateAttributes($personAttrs)*/;
        }
        return $this->configuration[(!isset($name) ? 0 : $name)];
    }

    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function getRepository()
    {
        return $this->getDoctrine()->getRepository($this->entityClass);
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
}
