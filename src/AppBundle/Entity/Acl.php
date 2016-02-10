<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Acl
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 * @ORM\Table(
 *      name="Acl",
 *      options={"collate"="utf8_swedish_ci"},
 *      indexes={
 *          @ORM\Index(
 *              name="idx_sourceEntry_id",
 *              columns={"sourceEntry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_targetEntry_id",
 *              columns={"targetEntry_id"}
 *          ),
 *          @ORM\Index(
 *              name="idx_acl",
 *              columns={
 *                  "viewAccess","createAccess","editAccess","deleteAccess"
 *              }
 *          )
 *      }
 * )
 * @ORM\Entity(
 *      repositoryClass="AppBundle\Entity\Repository\AclRepository"
 * )
 * @UniqueEntity(
 *      fields={"targetEntry", "sourceEntry"}
 * )
 */
class Acl
{
    /**
     * @var integer
     *
     * @ORM\Column(
     *      name="id",
     *      type="integer",
     *      nullable=false,
     *      options={"unsigned"=true}
     * )
     * @ORM\Id()
     * @ORM\GeneratedValue(
     *      strategy="IDENTITY"
     * )
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(
     *      name="viewAccess",
     *      type="boolean",
     *      nullable=true,
     *      options={"unsigned"=true},
     *      columnDefinition="TINYINT(1) UNSIGNED DEFAULT NULL"
     * )
     */
    private $viewAccess;

    /**
     * @var boolean
     *
     * @ORM\Column(
     *      name="createAccess",
     *      type="boolean",
     *      nullable=true,
     *      options={"unsigned"=true},
     *      columnDefinition="TINYINT(1) UNSIGNED DEFAULT NULL"
     * )
     */
    private $createAccess;

    /**
     * @var boolean
     *
     * @ORM\Column(
     *      name="editAccess",
     *      type="boolean",
     *      nullable=true,
     *      options={"unsigned"=true},
     *      columnDefinition="TINYINT(1) UNSIGNED DEFAULT NULL"
     * )
     */
    private $editAccess;

    /**
     * @var boolean
     *
     * @ORM\Column(
     *      name="deleteAccess",
     *      type="boolean",
     *      nullable=true,
     *      options={"unsigned"=true},
     *      columnDefinition="TINYINT(1) UNSIGNED DEFAULT NULL"
     * )
     */
    private $deleteAccess;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="targetEntry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $targetEntry;

    /**
     * @var Entry
     *
     * @ORM\ManyToOne(
     *      targetEntity="Entry"
     * )
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(
     *          name="sourceEntry_id",
     *          referencedColumnName="id",
     *          nullable=false,
     *          onDelete="CASCADE"
     *      )
     * })
     */
    private $sourceEntry;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set view access
     *
     * @param boolean $viewAccess
     *
     * @return self
     */
    public function setViewAccess($viewAccess)
    {
        $this->viewAccess = $viewAccess;

        return $this;
    }

    /**
     * Has view access
     *
     * @return boolean
     */
    public function hasViewAccess()
    {
        return $this->viewAccess;
    }

    /**
     * Set create access
     *
     * @param boolean $createAccess
     *
     * @return self
     */
    public function setCreateAccess($createAccess)
    {
        $this->createAccess = $createAccess;

        return $this;
    }

    /**
     * Has create access
     *
     * @return boolean
     */
    public function hasCreateAccess()
    {
        return $this->createAccess;
    }

    /**
     * Set edit access
     *
     * @param boolean $editAccess
     *
     * @return self
     */
    public function setEditAccess($edit)
    {
        $this->editAccess = $editAccess;

        return $this;
    }

    /**
     * Has edit access
     *
     * @return boolean
     */
    public function hasEditAccess()
    {
        return $this->editAccess;
    }

    /**
     * Set delete access
     *
     * @param boolean $deleteAccess
     *
     * @return self
     */
    public function setDeleteAccess($deleteAccess)
    {
        $this->deleteAccess = $deleteAccess;

        return $this;
    }

    /**
     * Has delete access
     *
     * @return boolean
     */
    public function hasDeleteAccess()
    {
        return $this->deleteAccess;
    }

    /**
     * Set target entry
     *
     * @param Entry $targetEntry
     *
     * @return self
     */
    public function setTargetEntry(Entry $targetEntry = null)
    {
        $this->targetEntry = $targetEntry;

        return $this;
    }

    /**
     * Get target entry
     *
     * @return Entry
     */
    public function getTargetEntry()
    {
        return $this->targetEntry;
    }

    /**
     * Set source entry
     *
     * @param Entry $sourceEntry
     *
     * @return self
     */
    public function setSourceEntry(Entry $sourceEntry = null)
    {
        $this->sourceEntry = $sourceEntry;

        return $this;
    }

    /**
     * Get source entry
     *
     * @return Entry
     */
    public function getSourceEntry()
    {
        return $this->sourceEntry;
    }
}

