<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Common\Interfaces\OrganizationInterface;
use AppBundle\Entity\Common\Traits\OrganizationTrait;

/**
 * Member organization
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\MemberOrganizationRepository"
 * )
 */
class MemberOrganization extends Entry implements OrganizationInterface
{
    use OrganizationTrait;
}
