<?php
namespace AppBundle\Entity\Repository\Common\Traits;


/**
 * Trait for SCRUD services.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
trait ScrudTrait
{
    use ValidationTrait;

    protected function search(
        User $user,
        array $request,
        &$message,
        &$foundCount
    ) {
        if ($message = $this->validateSearch($request, $config)) {
            return null;
        }

        $filter = $order = [];
        if (isset($request['filter'])) {
            $filter = array_intersect_key(
                $request['filter'],
                $config->getFilterAttributes()
            );
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

        $repo = $this->getDoctrine()->getRepository($config->getEntityClass());

        return $this->findByFilter(
            $filter,
            $order,
            $limit,
            $offset,
            $foundCount
        );
    }

    public function create(User $user, array $request, &$message)
    {
        $config = $this->getMappedConfiguration($request, $message, true);
        if (!$config) {
            return null;
        }

        if ($message = $this->validateCreate($request, $config)) {
            return null;
        }

        $entityClass = $config->getEntityClass();
        $item = new $entityClass();

        // Apply constraints
        if ($constraints = $config->getCreateConstraints()) {
            $request = array_merge($request, $constraints);
        }

        if ($message = $this->prepareItem($item, $request, $config)) {
            return null;
        }

        $em = $this->getManager();
        $em->persist($item);
        $em->flush();

        return $item;
    }

    public function read(User $user, array $request, &$message)
    {
        $config = $this->getMappedConfiguration($request, $message, false);
        if (!$config) {
            return null;
        }

        if ($message = $this->validateRead($request, $config)) {
            return null;
        }

        // Apply constraints
        $readRequest = array_intersect_key(
            $request,
            array_flip($config->getReadAttributes())
        );
        if ($constraints = $config->getReadConstraints()) {
            $readRequest = array_merge($readRequest, $constraints);
        }

        $repo = $this->getDoctrine()->getRepository($config->getEntityClass());

        $items = $repo->findByFilter($readRequest);
        if (count($items) !== 1) {
            $message = array_fill_keys(array_keys($request), 'Not found');
            return null;
        }

        return $items[0];
    }

    public function update(User $user, array $request, &$message)
    {
        $config = $this->getMappedConfiguration($request, $message, false);
        if (!$config) {
            return null;
        }

        $readRequest = array_intersect_key(
            $request,
            array_flip($config->getReadAttributes())
        );

        $item = $this->readItem($readRequest, $message);
        if (!isset($item)) {
            return JSendResponse::fail($message)->asArray();
        }

        $message = $this->validateUpdate($request, $config);
        if (!empty($message)) {
            return false;
        }

        // Apply constraints
        if ($constraints = $config->getUpdateConstraints($request)) {
            $request = array_merge($request, $constraints);
        }

        $message = $this->prepareItem($item, $request, $config);
        if (!empty($message)) {
            return false;
        }

        $this->getDoctrine()->getManager()->flush();

        return true;
    }

    public function delete(User $user, array $request, &$message)
    {
        $config = $this->getMappedConfiguration($request, $message, false);
        if (!$config) {
            return null;
        }

        $readRequest = array_intersect_key(
            $request,
            array_flip($config->getReadAttributes())
        );
        $item = $this->read($user, $readRequest, $message);
        if (!isset($item)) {
            return JSendResponse::fail($message)->asArray();
        }

        $message = $this->validateDelete($request, $config);
        if (!empty($message)) {
            return false;
        }

        // Apply constraints
        if ($constraints = $config->getDeleteConstraints()) {
            $request = array_merge($request, $constraints);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();

        return true;
    }

    protected function prepare(
        $item,
        array $request,
        ScrudConfiguration $config
    ) {
        $attributes = [];
        if ($item->getId()) {
            $clearMissing = false;
            $attributes = $config->getUpdateAttributes();
            $attributes = array_intersect(array_keys($request), $attributes);
        } else {
            $attributes = $config->getCreateAttributes();
            $clearMissing = true;
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
