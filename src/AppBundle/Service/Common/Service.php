<?php
namespace AppBundle\Service\Common;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use AppBundle\Service\Common\Interfaces\ServiceInterface;
use AppBundle\Service\Common\Traits\ServiceTrait;

abstract class Service implements
    ContainerAwareInterface,
    ServiceInterface
{
    use ContainerAwareTrait;
    use ServiceTrait;
}
