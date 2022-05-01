<?php declare(strict_types=1);

namespace App\Normalizer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ArrayCollectionNormalizer extends AbstractCustomNormalizer
{
    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Collection;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return mixed
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return array_map(
            fn($item) => $this->normalizer->normalize($item, $format, $context),
            $object->getValues()
        );
    }
}
