<?php declare(strict_types=1);

namespace App\Service;

use App\Exception\RestApiException;
use App\Model\ApiSchemaInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ApiSchemaResolver implements ArgumentValueResolverInterface
{
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        try {
            $reflection = new \ReflectionClass($argument->getType() ?? '');
            $result = $reflection->implementsInterface(ApiSchemaInterface::class);
        } catch (\ReflectionException $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     * @throws RestApiException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        try {
            $content = $request->getContent() ?: '{}';

            yield $this->serializer->deserialize($content, $argument->getType(), JsonEncoder::FORMAT);
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }
    }
}
