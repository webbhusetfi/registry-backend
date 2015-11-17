<?php
namespace AppBundle\Service\Common\Traits;

use AppBundle\Service\Configuration\ScrudConfigurationNG;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use JSend\JSendResponse;

/**
 * Trait for SCRUD services.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ScrudTraitNG
{
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

    protected function handlePost($item, array $request)
    {
        $attributes = $this->getConfiguration()->getAttributes();
        $clearMissing = true;

        if ($item->getId()) {
            // Update, support missing fields
            $attributes = array_intersect(array_keys($request), $attributes);
            $clearMissing = false;
        }

        $builder = $this->createFormBuilder($item);

        foreach ($attributes as $attribute) {
            $builder->add($attribute);
        }

        $form = $builder->getForm()->submit($request, $clearMissing);
        if (!$form->isValid()) {
            return $this->getFormErrors($form);
        }
        return [];
    }

    protected function validateAttributes(
        array $attributes,
        $allowed,
        $required,
        $in,
        $constraints
    ) {
        $keys = array_keys($attributes);
        $messages = [];

        // Validate allowed
        if (isset($allowed)
            && ($unallowed = array_diff($keys, $allowed))) {
            $messages = array_merge(
                $messages,
                array_fill_keys($unallowed, 'Unallowed attribute')
            );
        }

        // Validate required
        if (isset($required)
            && ($missing = array_diff($required, $keys))) {
            $messages = array_merge(
                $messages,
                array_fill_keys($missing, 'Attribute required')
            );
        }

        // Validate constraints
        if (isset($constraints)) {
            $invalid = array_diff_assoc(
                array_intersect_key($attributes, $constraints),
                $constraints
            );
            if ($invalid = array_diff_key($invalid, $messages)) {
                $messages = array_merge(
                    $messages,
                    array_fill_keys(array_keys($invalid), 'Unallowed value')
                );
            }
        }

        return $messages;
    }

    protected function validateSearch(
        array $request,
        ScrudConfigurationNG $config
    ) {
        $messages = [];

        // Validate request
        $allowed = ['filter', 'order', 'offset', 'limit'];
        if ($unallowed = array_diff(array_keys($request), $allowed)) {
            $messages = array_fill_keys($unallowed, 'Unallowed attribute');
        }

        if (!empty($request['filter']) && is_array($request['filter'])) {
            // Validate filter
            $msgs = $this->validateAttributes(
                $request['filter'],
                $config->getFilterAllowed(),
                $config->getFilterRequired(),
                $config->getFilterIn(),
                $config->getFilterConstraints()
            );
            if (!empty($msgs)) {
                $messages['filter'] = $msgs;
            }
        }


        if (!empty($request['order']) && is_array($request['order'])) {
            // Order validation
            $msgs = $this->validateAttributes(
                $request['order'],
                $config->getOrderAllowed(),
                $config->getOrderRequired(),
                $config->getOrderIn(),
                $config->getOrderConstraints()
            );
            if (!empty($msgs)) {
                $messages['order'] = $msgs;
            }
        }

        return $messages;
    }

    public function search(array $request)
    {
        $config = $this->getConfiguration();

        $messages = $this->validateSearch($request, $config);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        $filter = $order = [];
        if (!empty($request['filter']) && is_array($request['filter'])) {
            $filter = $request['filter'];
        }
        if (!empty($request['order']) && is_array($request['order'])) {
            $order = $request['order'];
        }

        $offset = $limit = null;
        if (isset($request['offset'])) {
            $offset = $request['offset'];
        }
        if (isset($request['limit'])) {
            $limit = $request['limit'];
        }

        // Apply constraints
        if ($constraints = $config->getFilterConstraints()) {
            $filter = array_merge($filter, $constraints);
        }

        $repo = $this->getDoctrine()->getRepository($config->getName());

        $criteria = $repo->buildCriteria($filter, $order, $offset, $limit);

        $foundCount = $repo->foundCount($criteria);

        $response = ['items' => [], 'foundCount' => 0];

        if ($foundCount) {
            $items = $repo->matching($criteria)->toArray();
            $response['items'] = $items;
            $response['foundCount'] = $foundCount;
        }

        return JSendResponse::success($response)->asArray();
    }


    protected function validateCreate(
        array $request,
        ScrudConfigurationNG $config
    ) {
        return $this->validateAttributes(
            $request,
            $config->getCreateAllowed(),
            $config->getCreateRequired(),
            $config->getCreateIn(),
            $config->getCreateConstraints()
        );
    }

    public function create(array $request)
    {
        $config = $this->getConfiguration();

        $entityClass = $config->getName();
        $item = new $entityClass();

        $messages = $this->validateCreate($request, $config);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        // Apply constraints
        if ($constraints = $config->getCreateConstraints()) {
            $request = array_merge($request, $constraints);
        }

        $messages = $this->handlePost($item, $request);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($item);
        $em->flush();

        return JSendResponse::success(['item' => $item])->asArray();
    }

    protected function validateRead(
        array $request,
        ScrudConfigurationNG $config
    ) {
        return $this->validateAttributes(
            $request,
            $config->getReadAllowed(), // $config->getIdentifier(),
            $config->getReadRequired(), // $config->getIdentifier(),
            $config->getReadIn(),
            $config->getReadConstraints() // $config->getFilterConstraints()
        );
    }

    public function read(array $request)
    {
        $config = $this->getConfiguration();

        $messages = $this->validateRead($request, $config);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        // Apply constraints
        if ($constraints = $config->getFilterConstraints()) {
            $request = array_merge($request, $constraints);
        }

        $repo = $this->getDoctrine()->getRepository($config->getName());

        $criteria = $repo->buildCriteria($request);

        $items = $repo->matching($criteria)->toArray();

        if (count($items) !== 1) {
            return JSendResponse::fail(['item' => 'Not found'])->asArray();
        }

        return JSendResponse::success(['item' => $items[0]])->asArray();
    }

    protected function validateUpdate(
        array $request,
        ScrudConfigurationNG $config
    ) {
        return $this->validateAttributes(
            $request,
            $config->getUpdateAllowed(),
            $config->getUpdateRequired(), // $config->getIdentifier(),
            $config->getUpdateIn(),
            $config->getUpdateConstraints()
        );
    }

    public function update(array $request)
    {
        $config = $this->getConfiguration();

        $messages = $this->validateUpdate($request, $config);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        $readRequest = array_intersect_key(
            $request,
            array_flip($config->getReadAllowed())
        );

        // Apply constraints
        if ($constraints = $config->getUpdateConstraints()) {
            $readRequest = array_merge($readRequest, $constraints);
        }

        $repo = $this->getDoctrine()->getRepository($config->getName());

        $criteria = $repo->buildCriteria($readRequest);

        $items = $repo->matching($criteria)->toArray();
        if (count($items) !== 1) {
            return JSendResponse::fail(['item' => 'Not found'])->asArray();
        }

        $messages = $this->handlePost($items[0], $request);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        $this->getDoctrine()->getManager()->flush();

        return JSendResponse::success()->asArray();
    }

    public function delete(array $request)
    {
        $config = $this->getConfiguration();

        $messages = $this->validateRead($request, $config);
        if (!empty($messages)) {
            return JSendResponse::fail($messages)->asArray();
        }

        // Apply constraints
        if ($constraints = $config->getFilterConstraints()) {
            $request = array_merge($request, $constraints);
        }

        $repo = $this->getDoctrine()->getRepository($config->getName());

        $criteria = $repo->buildCriteria($request);

        $items = $repo->matching($criteria)->toArray();
        if (count($items) !== 1) {
            return JSendResponse::fail(['item' => 'Not found'])->asArray();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($items[0]);
        $em->flush();

        return JSendResponse::success()->asArray();
    }
}
