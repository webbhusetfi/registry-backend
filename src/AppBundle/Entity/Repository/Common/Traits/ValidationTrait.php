<?php
namespace AppBundle\Entity\Repository\Common\Traits;

/**
 * Trait for repository SCRUD validation.
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

    /**
     * List all errors of a given form.
     *
     * @param Form $form
     *
     * @return array
     */
    protected function getFormErrors(Form $form)
    {
        $errors = [];
        foreach ($form->getIterator() as $key => $child) {

            foreach ($child->getErrors() as $error){
                if ($message = $error->getMessage()) {
                    $errors[$key] = $message;
                }
            }

            if (count($child->getIterator()) > 0) {
                if ($messages = $this->getFormErrors($child)) {
                    $errors[$key] = $messages;
                }
            }
        }
        return $errors;
    }

    protected function validateSearch(
        array $request,
        ScrudConfiguration $config
    ) {
        $messages = [];

        // Validate request
        $allowed = ['filter', 'order', 'offset', 'limit'];
        if ($msgs = $this->validateAllowed($request, $allowed)) {
            $messages = $msgs;
        }

        // Validate filter
        if (isset($request['filter'])) {
            if (!is_array($request['filter'])) {
                $messages['filter'] = 'Invalid value';
            } else {
                $msgs = $this->validate(
                    $request['filter'],
                    $config->getFilterAllowed(),
                    $config->getFilterRequired(),
                    $config->getFilterIn(),
                    $config->getFilterConstraints()
                );
                if ($msgs) {
                    $messages['filter'] = $msgs;
                }
            }
        }

        // Validate order
        if (isset($request['order'])) {
            if (!is_array($request['order'])) {
                $messages['order'] = 'Invalid value';
            } else {
                $msgs = $this->validate(
                    $request['order'],
                    $config->getOrderAllowed(),
                    $config->getOrderRequired(),
                    $config->getOrderIn(),
                    $config->getOrderConstraints()
                );
                if ($msgs) {
                    $messages['order'] = $msgs;
                }
            }
        }

        if (!empty($messages)) {
            return $messages;
        }
        return null;
    }

    protected function validateCreate(
        array $request,
        ScrudConfiguration $config
    ) {
        return $this->validate(
            $request,
            $config->getCreateAllowed(),
            $config->getCreateRequired(),
            $config->getCreateIn(),
            $config->getCreateConstraints()
        );
    }

    protected function validateRead(
        array $request,
        ScrudConfiguration $config
    ) {
        return $this->validate(
            $request,
            $config->getReadAllowed(),
            $config->getReadRequired(),
            $config->getReadIn(),
            $config->getReadConstraints()
        );
    }

    protected function validateUpdate(
        array $request,
        ScrudConfiguration $config
    ) {
        return $this->validate(
            $request,
            $config->getUpdateAllowed(),
            $config->getUpdateRequired(),
            $config->getUpdateIn(),
            $config->getUpdateConstraints()
        );
    }

    protected function validateDelete(
        array $request,
        ScrudConfiguration $config
    ) {
        return $this->validate(
            $request,
            $config->getDeleteAllowed(),
            $config->getDeleteRequired(),
            $config->getDeleteIn(),
            $config->getDeleteConstraints()
        );
    }
}
