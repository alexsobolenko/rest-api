<?php declare(strict_types=1);

namespace App\Exception;

use App\Utils\ArrayUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RestApiException extends \Exception
{
    private array $messages = [];

    /**
     * @param string $message
     * @param int $code
     * @return RestApiException
     */
    public static function string(string $message, int $code = Response::HTTP_BAD_REQUEST): RestApiException
    {
        $ex = new self('', $code);
        $ex->messages[] = $message;

        return $ex;
    }

    /**
     * @param array $messages
     * @return RestApiException
     */
    public static function array(array $messages): RestApiException
    {
        $ex = new self('', Response::HTTP_BAD_REQUEST);
        $ex->messages = $messages;

        return $ex;
    }

    /**
     * @param ConstraintViolationListInterface $object
     * @return RestApiException
     */
    public static function constraints(ConstraintViolationListInterface $object): RestApiException
    {
        return self::array(ArrayUtils::constraintsToArray($object));
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
