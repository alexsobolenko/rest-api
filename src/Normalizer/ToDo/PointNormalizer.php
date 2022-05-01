<?php declare(strict_types=1);

namespace App\Normalizer\ToDo;

use App\Entity\ToDo\Point;
use App\Exception\RestApiException;
use App\Normalizer\AbstractCustomNormalizer;
use App\Utils\DateTimeUtils;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PointNormalizer extends AbstractCustomNormalizer
{
    public const CONTEXT_TYPE_KEY = 'point';
    public const TYPE_DETAILS = 'point.details';

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Point;
    }

    /**
     * @param Point $object
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        try {
            return match ($this->getType($context)) {
                self::TYPE_DETAILS => [
                    'id' => $object->getId(),
                    'title' => $object->getTitle(),
                    'completed' => $object->isCompleted(),
                    'created' => DateTimeUtils::format($object->getCreatedAt(), DateTimeUtils::F_DATE_TIME),
                    'updated' => DateTimeUtils::format($object->getUpdatedAt(), DateTimeUtils::F_DATE_TIME),
                ],
                default => [
                    'id' => $object->getId(),
                ],
            };
        } catch (\Throwable $e) {
            throw new RestApiException($e->getMessage());
        }
    }
}
