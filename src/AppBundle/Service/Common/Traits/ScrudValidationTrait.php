<?php
namespace AppBundle\Service\Common\Traits;

use AppBundle\Service\Configuration\ScrudConfiguration;
use Symfony\Component\Form\Form;

/**
 * Trait for SCRUD service validation.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ScrudValidationTrait
{
    use ValidationTrait;

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
