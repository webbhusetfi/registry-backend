<?php
namespace AppBundle\Service\Configuration;

class ScrudConfiguration extends Configuration
{
    /**
     * Doctrine Registry service.
     *
     * @var Registry
     */
    private $doctrine;

    /**
     * Entity metadata.
     *
     * @var ClassMetadata
     */
    private $metaData;

    /**
     * List of constraints.
     *
     * @var array|null
     */
    private $constraints;

    /**
     * List of attributes for order.
     *
     * @var array|null
     */
    private $orderAttributes;

    /**
     * List of constraints for order.
     *
     * @var array|null
     */
    private $orderConstraints;

    /**
     * List of attributes for filter.
     *
     * @var array|null
     */
    private $filterAttributes;

    /**
     * List of constraints for filter.
     *
     * @var array|null
     */
    private $filterConstraints;

    /**
     * List of attributes for create.
     *
     * @var array|null
     */
    private $createAttributes;

    /**
     * List of constraints for create.
     *
     * @var array|null
     */
    private $createConstraints;

    /**
     * List of attributes for update.
     *
     * @var array|null
     */
    private $updateAttributes;

    /**
     * List of constraints for update.
     *
     * @var array|null
     */
    private $updateConstraints;

    /**
     * Constructor.
     *
     * @param Registry $doctrine
     * @param string $entityClass
     * @param array $methods
     */
    public function __construct(
        $doctrine,
        $entityClass,
        array $methods = ['search', 'create', 'read', 'update', 'delete']
    ) {
        $this->doctrine = $doctrine;
        $this->metaData = $doctrine->getManager()
            ->getClassMetadata($entityClass);

        $this->setMethods($methods);
    }

    /**
     * Get entity class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->metaData->name;
    }

    /**
     * Get identifier.
     *
     * @return array
     */
    public function getIdentifier()
    {
        return $this->metaData->identifier;
    }

    /**
     * Get attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return array_merge(
            array_keys($this->metaData->fieldMappings),
            array_keys($this->metaData->associationMappings)
        );
    }

    /**
     * Set constraints.
     *
     * @param array|null $constraints
     *
     * @return self
     */
    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;

        return $this;
    }

    /**
     * Get constraints.
     *
     * @return array|null
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * Set attributes for order.
     *
     * @param array|null $attributes
     *
     * @return self
     */
    public function setOrderAttributes($attributes)
    {
        $this->orderAttributes = $attributes;

        return $this;
    }

    /**
     * Get attributes for order.
     *
     * @return array
     */
    public function getOrderAttributes()
    {
        if (isset($this->orderAttributes)) {
            return $this->orderAttributes;
        }
        return $this->getAttributes();
    }

    /**
     * Set constraints for order.
     *
     * @param array|null $constraints
     *
     * @return self
     */
    public function setOrderConstraints($constraints)
    {
        $this->orderConstraints = $constraints;

        return $this;
    }

    /**
     * Get constraints for order.
     *
     * @return array|null
     */
    public function getOrderConstraints()
    {
        return $this->orderConstraints;
    }

    /**
     * Set attributes for filter.
     *
     * @param array|null $attributes
     *
     * @return self
     */
    public function setFilterAttributes($attributes)
    {
        $this->filterAttributes = $attributes;

        return $this;
    }

    /**
     * Get attributes for filter.
     *
     * @return array|null
     */
    public function getFilterAttributes()
    {
        if (isset($this->filterAttributes)) {
            return $this->filterAttributes;
        }
        return $this->getAttributes();
    }

    /**
     * Set constraints for filter.
     *
     * @param array|null $constraints
     *
     * @return self
     */
    public function setFilterConstraints($constraints)
    {
        $this->filterConstraints = $constraints;

        return $this;
    }

    /**
     * Get constraints for filter.
     *
     * @return array|null
     */
    public function getFilterConstraints()
    {
        if (isset($this->filterConstraints)) {
            return $this->filterConstraints;
        }
        return $this->constraints;
    }

    /**
     * Set attributes for create.
     *
     * @param array|null $attributes
     *
     * @return self
     */
    public function setCreateAttributes($attributes)
    {
        $this->createAttributes = $attributes;

        return $this;
    }

    /**
     * Get attributes for create.
     *
     * @return array|null
     */
    public function getCreateAttributes()
    {
        if (isset($this->createAttributes)) {
            return $this->createAttributes;
        }
        return $this->getAttributes();
    }

    /**
     * Set constraints for create.
     *
     * @param array|null $constraints
     *
     * @return self
     */
    public function setCreateConstraints($constraints)
    {
        $this->createConstraints = $constraints;

        return $this;
    }

    /**
     * Get constraints for create.
     *
     * @return array|null
     */
    public function getCreateConstraints()
    {
        if (isset($this->createConstraints)) {
            return $this->createConstraints;
        }
        return $this->constraints;
    }

    /**
     * Set attributes for update.
     *
     * @param array|null $attributes
     *
     * @return self
     */
    public function setUpdateAttributes($attributes)
    {
        $this->updateAttributes = $attributes;

        return $this;
    }

    /**
     * Get attributes for update.
     *
     * @return array|null
     */
    public function getUpdateAttributes()
    {
        if (isset($this->updateAttributes)) {
            return array_merge($this->getIdentifier(), $this->updateAttributes);
        }
        return array_merge($this->getIdentifier(), $this->getAttributes());
    }

    /**
     * Set constraints for update.
     *
     * @param array|null $constraints
     *
     * @return self
     */
    public function setUpdateConstraints($constraints)
    {
        $this->updateConstraints = $constraints;

        return $this;
    }

    /**
     * Get constraints for update.
     *
     * @return array|null
     */
    public function getUpdateConstraints()
    {
        if (isset($this->updateConstraints)) {
            return $this->updateConstraints;
        }
        return $this->constraints;
    }
}
