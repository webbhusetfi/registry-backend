<?php
namespace AppBundle\Service\Common;

use AppBundle\Service\Common\Interfaces\ScrudInterface;
use AppBundle\Service\Common\Traits\ScrudTraitNG;

use AppBundle\Service\Common\Interfaces\DoctrineInterface;
use AppBundle\Service\Common\Traits\DoctrineTrait;

use AppBundle\Service\Common\Interfaces\FormInterface;
use AppBundle\Service\Common\Traits\FormTrait;

abstract class ScrudServiceNG extends Service implements
    ScrudInterface,
    DoctrineInterface,
    FormInterface
{
    use ScrudTraitNG;
    use DoctrineTrait;
    use FormTrait;
}
