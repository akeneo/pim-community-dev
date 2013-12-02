<?php

namespace Oro\Bundle\LocaleBundle\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class UTCDateTimeType extends DateTimeType
{
    /**
     * @var \DateTimeZone
     */
    static private $utcTimeZone = null;

    /**
     * PHP Time zone
     *
     * @var \DateTimeZone
     */
    static private $serverTimeZone = null;

    /**
     * Get datetime zone UTC
     *
     * @static
     * @return \DateTimeZone
     */
    private static function getUTCTimeZone()
    {
        if (!self::$utcTimeZone) {
            self::$utcTimeZone = new \DateTimeZone('UTC');
        }

        return self::$utcTimeZone;
    }

    /**
     * Get server time zone
     *
     * @static
     * @return \DateTimeZone
     */
    private static function getServerTimeZone()
    {
        if (!self::$serverTimeZone) {
            self::$serverTimeZone = new \DateTimeZone(date_default_timezone_get());
        }

        return self::$serverTimeZone;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $value->setTimeZone(self::getUTCTimeZone());

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $val = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUTCTimeZone()
        );
        $val->setTimeZone(self::getServerTimeZone());

        if (!$val) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        $errors = $val->getLastErrors();
        // date was parsed to completely not valid value
        if ($errors['warning_count'] > 0 && (int)$val->format('Y') < 0) {
            return null;
        }

        return $val;
    }
}
