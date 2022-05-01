<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\RestApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        $response = $this->createApiResponse($exception, $request->getPathInfo());
        $event->setResponse($response);
    }

    /**
     * @param \Throwable $exception
     * @param string $path
     * @return JsonResponse
     */
    private function createApiResponse(\Throwable $exception, string $path): JsonResponse
    {
        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $data = [
            'timestamp' => time(),
            'status' => (int) $statusCode,
            'path' => $path,
        ];

        if ($exception instanceof RestApiException) {
            $data['errors'] = $exception->getMessages();
            $data['status'] = $exception->getCode();
        } else {
            $data['errors'] = [$exception->getMessage()];
        }

        $this->logger->error('Rest api exception', $data);

        return new JsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
