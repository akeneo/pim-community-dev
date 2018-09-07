<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer;

use Oro\Bundle\PimDataGridBundle\Normalizer\DateTimeNormalizer;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    const TEST_TIMEZONE = 'Europe/Paris';

    protected $userTimezone;

    function let(NormalizerInterface $standardNormalizer, PresenterInterface $presenter, UserContext $userContext)
    {
        $this->userTimezone = date_default_timezone_get();
        date_default_timezone_set(self::TEST_TIMEZONE);

        $this->beConstructedWith($standardNormalizer, $presenter, $userContext);
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
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_normalization_on_datetimes_only()
    {
        $datetime = new \DateTime('NOW');
        $this->supportsNormalization($datetime, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($datetime, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_datetimes_with_paris_timezone($standardNormalizer, $presenter, $userContext)
    {
        $datetime = new \DateTime('2015-01-01 23:50:00');
        $timezone = new \DateTimeZone('Europe/Paris');
        $datetime->setTimezone($timezone);

        $standardNormalizer->normalize($datetime, 'standard', [])->willReturn('2015-01-01T23:50:00+01:00');
        $userContext->getUiLocaleCode()->willReturn('en_US');
        $userContext->getUserTimezone()->willReturn('Pacific/Kiritimati');
        $presenter->present(
            '2015-01-01T23:50:00+01:00',
            [
                'locale'   => 'en_US',
                'timezone' => 'Pacific/Kiritimati',
            ]
        )->willReturn('01/01/2015');

        $this->normalize($datetime, 'datagrid')->shouldReturn('01/01/2015');
    }

    function it_normalizes_datetimes_with_new_york_timezone($standardNormalizer, $presenter, $userContext)
    {
        $datetime = new \DateTime('2015-01-01');
        $timezone = new \DateTimeZone('America/New_York');
        $datetime->setTimezone($timezone);

        $standardNormalizer->normalize($datetime, 'standard', [])->willReturn('2014-12-31T18:00:00-05:00');
        $userContext->getUiLocaleCode()->willReturn('en_US');
        $userContext->getUserTimezone()->willReturn('Pacific/Kiritimati');
        $presenter->present(
            '2014-12-31T18:00:00-05:00',
            [
                'locale'   => 'en_US',
                'timezone' => 'Pacific/Kiritimati',
            ]
        )->willReturn('12/31/2014');

        $this->normalize($datetime, 'datagrid')->shouldReturn('12/31/2014');
    }
}
