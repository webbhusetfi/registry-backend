<?php
namespace AppBundle\Kernel;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class JsonExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $statusCode = 500;
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof AccessDeniedException) {
            $statusCode = JsonResponse::HTTP_FORBIDDEN;
        }

        $content = [];
        if ($statusCode == 500) {
            $message = $exception->getMessage();
            if (empty($message)) {
                $message = get_class($exception) . ' thrown ';
            }
            $message .= ' on line ' . $exception->getLine();
            $message .= ' in ' . $exception->getFile();
            if ($code = $exception->getCode()) {
                $message .= " ({$code})";
            }
            $content['message'] = $message;
            //$content['trace'] = $exception->getTraceAsString();
        } else {
            $content['message'] = $exception->getMessage();
        }

        $event->setResponse(new JsonResponse($content, $statusCode));
    }
}
