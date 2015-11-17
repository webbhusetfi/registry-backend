<?php
namespace AppBundle\Service\Common;

use AppBundle\Service\Common\Interfaces\ScrudInterface;
use AppBundle\Service\Common\Traits\ScrudTrait;

use AppBundle\Service\Common\Interfaces\DoctrineInterface;
use AppBundle\Service\Common\Traits\DoctrineTrait;

use AppBundle\Service\Common\Interfaces\FormInterface;
use AppBundle\Service\Common\Traits\FormTrait;

abstract class ScrudService extends Service implements
    ScrudInterface,
    DoctrineInterface,
    FormInterface
{
    use ScrudTrait;
    use DoctrineTrait;
    use FormTrait;
}
