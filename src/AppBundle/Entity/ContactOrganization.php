<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\Common\Interfaces\OrganizationInterface;
use AppBundle\Entity\Common\Traits\OrganizationTrait;

/**
 * Contact organization
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\ContactOrganizationRepository"
 * )
 */
class ContactOrganization extends Entry implements OrganizationInterface
{
    use OrganizationTrait;
}
