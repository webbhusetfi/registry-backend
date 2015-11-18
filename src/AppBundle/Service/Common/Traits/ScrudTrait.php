<?php
namespace AppBundle\Service\Common\Traits;

/**
 * Trait for SCRUD services.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ScrudTrait
{
    use ScrudValidationTrait;

    protected function searchItems(array $request, &$message, &$foundCount) {
        $config = $this->getConfiguration();

        if ($message = $this->validateSearch($request, $config)) {
            return null;
        }

        $filter = $order = [];
        if (isset($request['filter'])) {
            $filter = $request['filter'];
        }
        if (isset($request['order'])) {
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

        return $repo->findByFilter(
            $filter,
            $order,
            $limit,
            $offset,
            $foundCount
        );
    }

    protected function createItem(array $request, &$message)
    {
        $config = $this->getConfiguration();

        if ($message = $this->validateCreate($request, $config)) {
            return null;
        }

        $entityClass = $config->getName();
        $item = new $entityClass();

        // Apply constraints
        if ($constraints = $config->getCreateConstraints($request)) {
            $request = array_merge($request, $constraints);
        }

        if ($message = $this->prepareItem($item, $request)) {
            return null;
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($item);
        $em->flush();

        return $item;
    }

    protected function readItem(array $request, &$message)
    {
        $config = $this->getConfiguration();

        if ($message = $this->validateRead($request, $config)) {
            return null;
        }

        // Apply constraints
        $readRequest = $request;
        if ($constraints = $config->getReadConstraints()) {
            $readRequest = array_merge($readRequest, $constraints);
        }

        $repo = $this->getDoctrine()->getRepository($config->getName());

        $items = $repo->findByFilter($readRequest);
        if (count($items) !== 1) {
            $message = array_fill_keys(array_keys($request), 'Not found');
            return null;
        }

        return $items[0];
    }

    protected function updateItem($item, array $request, &$message)
    {
        $config = $this->getConfiguration();

        $message = $this->validateUpdate($item, $request, $config);
        if (!empty($message)) {
            return false;
        }

        // Apply constraints
        if ($constraints = $config->getUpdateConstraints($request)) {
            $request = array_merge($request, $constraints);
        }

        $message = $this->prepareItem($item, $request);
        if (!empty($message)) {
            return false;
        }

        $this->getDoctrine()->getManager()->flush();

        return true;
    }

    protected function deleteItem($item, array $request, &$message)
    {
        $config = $this->getConfiguration();

        $message = $this->validateDelete($item, $request, $config);
        if (!empty($message)) {
            return false;
        }

        // Apply constraints
        if ($constraints = $config->getDeleteConstraints($request)) {
            $request = array_merge($request, $constraints);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();

        return true;
    }

    protected function prepareItem($item, array $request)
    {
        $attributes = $this->getConfiguration()->getAttributes($request);
        $clearMissing = true;

        if ($item->getId()) {
            // Update, support missing fields
            $attributes = array_intersect(array_keys($request), $attributes);
            $clearMissing = false;
        }

        $builder = $this->container->get('form.factory')
            ->createBuilder('form', $item);

        foreach ($attributes as $attribute) {
            $builder->add($attribute);
        }

        $form = $builder->getForm()->submit($request, $clearMissing);
        if (!$form->isValid()) {
            return $this->getFormErrors($form);
        }
        return null;
    }
}
