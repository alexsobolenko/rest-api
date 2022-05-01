<?php declare(strict_types=1);

namespace App\Utils;

use App\Exception\RestApiException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ArrayUtils
{
    /**
     * @param ConstraintViolationListInterface $object
     * @return array
     * @throws \Exception
     */
    public static function constraintsToArray(ConstraintViolationListInterface $object): array
    {
        try {
            $result = [];
            if ($object instanceof ConstraintViolationList) {
                foreach ($object->getIterator() as $error) {
                    $result[] = $error->getMessage();
                }
            }
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }

        return $result;
    }
}
