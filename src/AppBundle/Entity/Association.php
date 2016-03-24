<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Common\Interfaces\OrganizationInterface;
use AppBundle\Entity\Common\Traits\OrganizationTrait;

/**
 * Assocation
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\AssociationRepository"
 * )
 */
class Association extends Entry implements OrganizationInterface
{
    use OrganizationTrait;
}
