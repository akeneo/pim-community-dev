<?php

declare(strict_types=1);

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\Translation\TranslatorInterface;

class DateTimeWithUserTimezonePropertySpec extends ObjectBehavior
{
    function let(
        TranslatorInterface $translator,
        PresenterInterface $presenter,
        UserContext $userContext
    ) {
        $this->beConstructedWith($translator, $presenter, $userContext);

        $this->init(PropertyConfiguration::create([
            'name'          => 'a_date',
            'label'         => 'A date',
            'type'          => 'datetime_with_user_timezone',
            'frontend_type' => 'datetime',
        ]));
    }

    function it_formats_a_datetime_with_user_timezone(
        $userContext,
        $presenter
    ) {
        $datetime = new \DateTime('2018-03-20T18:13');

        $userContext->getUiLocaleCode()->willReturn('en_GB');
        $userContext->getUserTimezone()->willReturn('Pacific/Kiritimati');
        $presenter->present(
            $datetime,
            [
                'locale'   => 'en_GB',
                'timezone' => 'Pacific/Kiritimati'
            ]
        )->shouldBeCalled();

        $this->getValue(new ResultRecord(['a_date' => $datetime]));
    }
}
