<?php declare(strict_types=1);

namespace App\Utils;

use App\Exception\RestApiException;

class DateTimeUtils
{
    public const F_DATE = 'd.m.Y';
    public const F_DATE_TIME = 'd.m.Y H:i:s';

    /**
     * @return \DateTimeImmutable
     * @throws RestApiException
     */
    public static function now(): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable('now');
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }
    }

    /**
     * @param \DateTimeInterface|null $value
     * @param string $format
     * @return string|null
     * @throws RestApiException
     */
    public static function format(?\DateTimeInterface $value, string $format): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return $value->format($format);
        } catch (\Throwable $e) {
            throw RestApiException::string($e->getMessage());
        }
    }
}
