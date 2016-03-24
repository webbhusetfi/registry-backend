<?php
namespace AppBundle\Entity\Common\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description trait
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait DescriptionTrait
{
    /**
     * @var string
     *
     * @ORM\Column(
     *      name="description",
     *      type="string",
     *      length=255
     * )
     * @Assert\Length(max = 255)
     */
    protected $description;

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }
}
