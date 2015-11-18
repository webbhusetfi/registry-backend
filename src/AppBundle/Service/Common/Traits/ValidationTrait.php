<?php
namespace AppBundle\Service\Common\Traits;

/**
 * Trait for service request validation.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ValidationTrait
{
    protected function validateAllowed(
        array $attributes,
        array $allowed,
        $message = 'Unallowed attribute'
    ) {
        if ($unallowed = array_diff(array_keys($attributes), $allowed)) {
            return array_fill_keys($unallowed, $message);
        }
        return null;
    }

    protected function validateRequired(
        array $attributes,
        array $required,
        $message = 'Required attribute'
    ) {
        if ($missing = array_diff($required, array_keys($attributes))) {
            return array_fill_keys($missing, $message);
        }
        return null;
    }

    protected function validateIn(
        array $attributes,
        array $in,
        $message = 'Unallowed value'
    ) {
        $messages = [];
        foreach ($in as $key => $values) {
            if (!array_key_exists($key, $attributes)
                || !in_array($attributes[$key], (array)$values)) {
                $messages[$key] = $message;
            }
        }
        if (!empty($messages)) {
            return $messages;
        }
        return null;
    }

    protected function validate(
        array $attributes,
        $allowed,
        $required,
        $in,
        $constraints
    ) {
        $messages = [];

        if (isset($allowed)) {
            if ($msgs = $this->validateAllowed($attributes, $allowed)) {
                $messages = array_merge($msgs, $messages);
            }
        }
        if (isset($required)) {
            if ($msgs = $this->validateRequired($attributes, $required)) {
                $messages = array_merge($msgs, $messages);
            }
        }
        if (isset($in)) {
            if ($msgs = $this->validateIn($attributes, $in)) {
                $messages = array_merge($msgs, $messages);
            }
        }
        if (isset($constraints)) {
            $constraints = array_intersect_key($constraints, $attributes);
            if ($msgs = $this->validateIn($attributes, $constraints)) {
                $messages = array_merge($msgs, $messages);
            }
        }

        if (!empty($messages)) {
            return $messages;
        }
        return null;
    }
}
