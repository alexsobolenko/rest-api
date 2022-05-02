<?php declare(strict_types=1);

namespace App\Normalizer\ToDo;

use App\Entity\ToDo\Task;
use App\Exception\RestApiException;
use App\Normalizer\AbstractCustomNormalizer;
use App\Utils\DateTimeUtils;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class TaskNormalizer extends AbstractCustomNormalizer
{
    public const CONTEXT_TYPE_KEY = 'task';
    public const TYPE_LIST = 'task.list';
    public const TYPE_DETAILS = 'task.details';

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Task;
    }

    /**
     * @param Task $object
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        try {
            return match ($this->getType($context)) {
                self::TYPE_LIST => [
                    'id' => $object->getId(),
                    'title' => $object->getTitle(),
                    'completed' => $object->getCompletedPercent(),
                    'priority' => $object->getPriority(),
                    'created' => DateTimeUtils::format($object->getCreatedAt(), DateTimeUtils::F_DATE_TIME),
                    'updated' => DateTimeUtils::format($object->getUpdatedAt(), DateTimeUtils::F_DATE_TIME),
                ],
                self::TYPE_DETAILS => [
                    'id' => $object->getId(),
                    'title' => $object->getTitle(),
                    'priority' => $object->getPriority(),
                    'points' => $this->normalizer->normalize($object->getPoints(), $format, $context),
                    'created' => DateTimeUtils::format($object->getCreatedAt(), DateTimeUtils::F_DATE_TIME),
                    'updated' => DateTimeUtils::format($object->getUpdatedAt(), DateTimeUtils::F_DATE_TIME),
                ],
                default => [
                    'id' => $object->getId(),
                ],
            };
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }
    }
}
