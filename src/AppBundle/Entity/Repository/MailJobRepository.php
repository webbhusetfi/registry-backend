<?php
namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Repository\Common\Interfaces\AssignerInterface;

use AppBundle\Entity\Repository\Common\Interfaces\FactoryInterface;
use AppBundle\Entity\Repository\Common\Interfaces\MetadataHelperInterface;
use AppBundle\Entity\Repository\Common\Interfaces\QueryHelperInterface;
use AppBundle\Entity\Repository\Common\Interfaces\SerializerInterface;
use AppBundle\Entity\Repository\Common\Interfaces\ValidatorInterface;
use AppBundle\Entity\Repository\Common\Traits\AssignerTrait;

use AppBundle\Entity\Repository\Common\Traits\FactoryTrait;
use AppBundle\Entity\Repository\Common\Traits\MetadataHelperTrait;
use AppBundle\Entity\Repository\Common\Traits\QueryHelperTrait;
use AppBundle\Entity\Repository\Common\Traits\SerializerTrait;
use AppBundle\Entity\Repository\Common\Traits\ValidatorTrait;
use Doctrine\ORM\EntityRepository;


/**
 * MailJob repository
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class MailJobRepository extends EntityRepository implements
    MetadataHelperInterface,
    QueryHelperInterface,
    FactoryInterface,
    AssignerInterface,
    ValidatorInterface,
    SerializerInterface
{
    use MetadataHelperTrait;
    use QueryHelperTrait;
    use FactoryTrait;
    use AssignerTrait;
    use ValidatorTrait;
    use SerializerTrait;
}
