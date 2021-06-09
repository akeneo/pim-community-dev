<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UTCDateTimeImmutableType extends DateTimeImmutableType
{
    public static ?\DateTimeZone $defaultTimeZone = null;

    private static ?\DateTimeZone $utc = null;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof \DateTimeImmutable) {
            $value = $value->setTimeZone(self::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $converted = \DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUtc()
        );

        if (!$converted) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
        }

        $converted = $converted->setTimezone(self::getDefaultTimeZone());

        return $converted;
    }

    private static function getUtc(): \DateTimeZone
    {
        return self::$utc ?: self::$utc = new \DateTimeZone('UTC');
    }

    private static function getDefaultTimeZone(): \DateTimeZone
    {
        return self::$defaultTimeZone ?: self::$defaultTimeZone = new \DateTimeZone(date_default_timezone_get());
    }
}
