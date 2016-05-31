<?php
namespace AppBundle\Service;

use AppBundle\Service\Common\JSendService;
use AppBundle\Service\Configuration\Configuration;
use JSend\JSendResponse;

class UserService extends JSendService
{
    public function create(array $request)
    {
        $message = null;
        $response = $this->getRepository()->create(
            $request,
            $this->getUser(),
            $message,
            $this->get('security.password_encoder')
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    public function update(array $request)
    {
        $message = null;
        $response = $this->getRepository()->update(
            $request,
            $this->getUser(),
            $message,
            $this->get('security.password_encoder'),
            $this->get("security.token_storage")
        );
        if (isset($response)) {
            return JSendResponse::success($response)->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }

    public function delete(array $request)
    {
        $message = null;
        $response = $this->getRepository()->delete(
            $request,
            $this->getUser(),
            $message,
            $this->get("security.token_storage")
        );
        if ($response) {
            return JSendResponse::success()->asArray();
        }
        return JSendResponse::fail($message)->asArray();
    }
}
