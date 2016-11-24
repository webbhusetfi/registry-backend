<?php
namespace AppBundle\Controller;

use JSend\JSendResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class ApiController extends Controller
{
    /**
     * @Route("/", name="root")
     * @Method("POST")
     */
    public function indexAction(Request $request)
    {
        $content = json_decode($request->getContent(), true);
        if (!is_array($content)) {
            throw new BadRequestHttpException('Invalid JSON request');
        }

        $results = [];
        foreach ($content as $key => $message) {
            if (isset($message['service'])) {
                list($name, $method) = explode('/', $message['service']);

                $serviceName = "registryapi.{$name}";
                if ($this->has($serviceName)) {
                    $service = $this->get($serviceName);
                    if ($service->getConfiguration()->hasMethod($method)) {
                        $results[$key] = $service->{$method}(
                            !empty($message['arguments'])
                            ? $message['arguments']
                            : []
                        );
                    } else {
                        $results[$key] = JSendResponse::error(
                            "Invalid method: {$method}"
                        )->asArray();
                    }
                } else {
                    $results[$key] = JSendResponse::error(
                        "Invalid service: {$name}"
                    )->asArray();
                }

                // Debug data
                $results[$key]['query'] = $message;
            }
        }

        if (empty($results)) {
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }
        return new JsonResponse($results);
    }

    /**
     * @Route("/login/", name="login")
     * @Method("POST")
     */
    public function loginAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON request');
        }

        if (empty($data['username']) || empty($data['password'])) {
            throw new BadRequestHttpException(
                'Username and password required.'
            );
        }

        $repo = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repo->findOneBy(['username' => $data['username']]);
        if (empty($user)) {
            throw new AccessDeniedHttpException('Invalid user.');
        }

        $encoder = $this->get('security.password_encoder');
        $valid = $encoder->isPasswordValid(
            $user,
            $data['password']
        );

        if (!$valid) {
            throw new AccessDeniedHttpException('Invalid user.');
        }

        // Login user to "main" section
        $token = new UsernamePasswordToken(
            $user,
            $user->getPassword(),
            "main",
            $user->getRoles()
        );
        $this->get("security.token_storage")->setToken($token);

        // Fire the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch(
            "security.interactive_login",
            $event
        );

        return new JsonResponse($user);
    }

    /**
     * @Route("/logout/", name="logout")
     * @Method("POST")
     */
    public function logoutAction(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
