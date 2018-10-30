<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    const TEST_TIMEZONE = 'Europe/Paris';

    protected $userTimezone;

    function let()
    {
        $this->userTimezone = date_default_timezone_get();
        date_default_timezone_set(self::TEST_TIMEZONE);
    }

    function letGo()
    {
        date_default_timezone_set($this->userTimezone);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DateTimeNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_normalization_on_datetimes_only()
    {
        $datetime = new \DateTime('NOW');
        $this->supportsNormalization($datetime, 'standard')->shouldReturn(true);
        $this->supportsNormalization($datetime, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
    }

    function it_normalizes_datetimes_with_paris_timezone()
    {
        $datetime = new \DateTime('2015-01-01 23:50:00');
        $timezone = new \DateTimeZone('Europe/Paris');
        $datetime->setTimezone($timezone);

        $this->normalize($datetime, 'standard')->shouldReturn('2015-01-01T23:50:00+01:00');
    }

    function it_normalizes_datetimes_with_new_york_timezone()
    {
        $datetime = new \DateTime('2015-01-01');
        $timezone = new \DateTimeZone('America/New_York');
        $datetime->setTimezone($timezone);

        $this->normalize($datetime, 'standard')->shouldReturn('2014-12-31T18:00:00-05:00');
    }
}
