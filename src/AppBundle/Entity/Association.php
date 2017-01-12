<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Common\Interfaces\OrganizationInterface;

use AppBundle\Entity\Common\Traits\OrganizationTrait;
use Doctrine\ORM\Mapping as ORM;

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
