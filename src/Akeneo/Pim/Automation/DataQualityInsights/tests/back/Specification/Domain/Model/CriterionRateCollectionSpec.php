<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriterionRateCollectionSpec extends ObjectBehavior
{
    public function it_is_a_rates_collection()
    {
        $this->shouldHaveType(CriterionRateCollection::class);
    }

    public function it_adds_rates_per_channel_and_locale()
    {
        $this
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(91))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(40))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(75))
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));

        $this->toArrayInt()->shouldReturn([
            'ecommerce' => [
                'en_US' => 91,
                'fr_FR' => 40,
            ],
            'print' => [
                'en_US' => 75,
                'fr_FR' => 65,
            ],
        ]);

        $this->toArrayString()->shouldReturn([
            'ecommerce' => [
                'en_US' => 'A',
                'fr_FR' => 'E',
            ],
            'print' => [
                'en_US' => 'C',
                'fr_FR' => 'D',
            ],
        ]);
    }

    public function it_returns_the_rate_for_a_channel_and_a_locale()
    {
        $expectedRate = new Rate(75);

        $this
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(91))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(40))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), $expectedRate)
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));

        $this->getByChannelAndLocale(new ChannelCode('print'), new LocaleCode('en_US'))->shouldReturn($expectedRate);
    }

    public function it_returns_null_if_there_is_no_rate_for_a_channel_and_a_locale()
    {
        $this
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(91))
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));

        $this->getByChannelAndLocale(new ChannelCode('print'), new LocaleCode('en_US'))->shouldReturn(null);
    }
}
