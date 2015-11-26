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
        if (!$this->updateItem($request, $message)) {
            return JSendResponse::fail($message)->asArray();
        }

        return JSendResponse::success()->asArray();
    }

    public function delete(array $request)
    {
        if (!$this->deleteItem($request, $message)) {
            return JSendResponse::fail($message)->asArray();
        }

        return JSendResponse::success()->asArray();
    }
}
