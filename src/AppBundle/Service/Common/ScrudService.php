<?php
namespace AppBundle\Service\Common;

use AppBundle\Service\Common\Interfaces\ScrudInterface;
use AppBundle\Service\Common\Traits\ScrudTrait;
use JSend\JSendResponse;

abstract class ScrudService extends Service implements ScrudInterface
{
    use ScrudTrait;

    public function search(array $request)
    {
        $items = $this->searchItems($request, $message, $foundCount);

        if (!isset($items)) {
            return JSendResponse::fail($message)->asArray();
        }

        return JSendResponse::success([
            'items' => $items,
            'foundCount' => $foundCount
        ])->asArray();
    }

    public function create(array $request)
    {
        $item = $this->createItem($request, $message);
        if (!isset($item)) {
            return JSendResponse::fail($message)->asArray();
        }

        return JSendResponse::success(['item' => $item])->asArray();
    }

    public function read(array $request)
    {
        $item = $this->readItem($request, $message);
        if (!isset($item)) {
            return JSendResponse::fail($message)->asArray();
        }

        return JSendResponse::success(['item' => $item])->asArray();
    }

    public function update(array $request)
    {
        $config = $this->getConfiguration();

        $readRequest = array_intersect_key(
            $request,
            array_flip($config->getReadAllowed())
        );
        $item = $this->readItem($readRequest, $message);
        if (!isset($item)) {
            return JSendResponse::fail($message)->asArray();
        }

        $updateRequest = array_diff_key(
            $request,
            array_flip($config->getReadAllowed())
        );
        if (!$this->updateItem($item, $updateRequest, $message)) {
            return JSendResponse::fail($message)->asArray();
        }

        return JSendResponse::success()->asArray();
    }

    public function delete(array $request)
    {
        $config = $this->getConfiguration();

        $readRequest = array_intersect_key(
            $request,
            array_flip($config->getReadAllowed())
        );
        $item = $this->readItem($readRequest, $message);
        if (!isset($item)) {
            return JSendResponse::fail($message)->asArray();
        }

        $updateRequest = array_diff_key(
            $request,
            array_flip($config->getReadAllowed())
        );
        if (!$this->deleteItem($item, $updateRequest, $message)) {
            return JSendResponse::fail($message)->asArray();
        }

        return JSendResponse::success()->asArray();
    }
}
