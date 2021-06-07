<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types\UTCDateTimeImmutableType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UTCDateTimeImmutableTypeSpec extends ObjectBehavior
{
    public function let(): void
    {
        UTCDateTimeImmutableType::$defaultTimeZone = new \DateTimeZone('America/Los_Angeles');
    }

    public function letGo(): void
    {
        UTCDateTimeImmutableType::$defaultTimeZone = new \DateTimeZone(date_default_timezone_get());
    }

    public function it_converts_a_timezoned_immutable_date_time_to_a_utc_date_string(AbstractPlatform $platform): void
    {
        $value = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2021-01-01T00:00:00+12:00');
        $platform->getDateTimeFormatString()
            ->willReturn('Y-m-d H:i:s');

        $expected = '2020-12-31 12:00:00';

        $this->convertToDatabaseValue($value, $platform)
            ->shouldReturn($expected);
    }

    public function it_throws_if_the_date_time_is_not_immutable(AbstractPlatform $platform): void
    {
        $value = \DateTime::createFromFormat(\DateTimeInterface::ATOM, '2021-01-01T00:00:00+12:00');

        $this->shouldThrow(ConversionException::class)
            ->during('convertToDatabaseValue', [$value, $platform]);
    }

    public function it_converts_a_utc_date_string_to_a_timezoned_immutable_date_time(AbstractPlatform $platform): void
    {
        $value = '2021-01-01 00:00:00';
        $platform->getDateTimeFormatString()
            ->willReturn('Y-m-d H:i:s');

        $expected = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-12-31T16:00:00-08:00');

        $this->convertToPHPValue($value, $platform)
            ->shouldBeLike($expected);
    }

    public function it_throws_if_the_date_string_format_is_invalid(AbstractPlatform $platform): void
    {
        $value = 'not_a_valid_date_format';
        $platform->getDateTimeFormatString()
            ->willReturn('Y-m-d H:i:s');

        $this->shouldThrow(ConversionException::class)
            ->during('convertToPHPValue', [$value, $platform]);
    }

    public function it_doesnt_convert_null_values(AbstractPlatform $platform): void
    {
        $this->convertToDatabaseValue(null, $platform)
            ->shouldReturn(null);

        $this->convertToPHPValue(null, $platform)
            ->shouldBeLike(null);
    }
}
