<?php
namespace AppBundle\Entity\Repository\Common\Traits;

use AppBundle\Entity\Common\Entity;
use Doctrine\ORM\Mapping\MappingException;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Symfony\Component\Validator\Validation;

/**
 * Trait implementing 
 * \AppBundle\Entity\Repository\Common\Interfaces\ValidatorInterface
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ValidatorTrait
{
    /**
     * {@inheritdoc}
     */
    public function validate(Entity $entity)
    {
        $errors = [];

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $messages = $validator->validate($entity);
        if (!empty($messages)) {
            foreach ($messages as $message) {
                $key = $message->getPropertyPath();
                if (!isset($errors[$key])) {
                    $errors[$key] = $message->getMessage();
                }
            }
        }

        $messages = $this->validateUniqueConstraints($entity);
        if (!empty($messages)) {
            $errors = array_merge($messages, $errors);
        }

        return $errors;
    }

    protected function validateUniqueConstraints(Entity $entity)
    {
        $errors = [];

        $constraints = $this->getUniqueConstraints();
        if (!empty($constraints)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($constraints as $fields) {
                foreach ($fields as $field) {
                    $value = $accessor->getValue($entity, $field);
                    if ($value instanceof Entity) {
                        $values[$field] = $value->getId();
                    } else {
                        $values[$field] = $value;
                    }
                }
                $found = $this->findBy($values);
                if (!empty($found)) {
                    if(!$entity->getId()
                        || $entity->getId() != $found[0]->getId()) {
                        $message = "Unique value required";
                        if (count($fields) > 1) {
                            $message .= "(" . implode($fields) . ")";
                        }
                        $errors = array_merge(
                            array_fill_keys($fields, $message), 
                            $errors
                        );
                    }
                }
            }
        }

        return $errors;
    }
}
