<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * Stores dates with UTC timezone
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UTCDateTimeType extends DateTimeType
{
    /** @var null|\DateTimeZone */
    private static $utc = null;

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $value->setTimeZone((self::$utc) ? self::$utc : (self::$utc = new \DateTimeZone('UTC')));

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $val = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            (self::$utc) ? self::$utc : (self::$utc = new \DateTimeZone('UTC'))
        );

        $serverTimezone = date_default_timezone_get();
        $val->setTimezone(new \DateTimeZone($serverTimezone));

        if (!$val) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        $errors = $val->getLastErrors();

        // date was parsed to completely not valid value
        if ($errors['warning_count'] > 0 && (int) $val->format('Y') < 0) {
            return null;
        }

        return $val;
    }
}
