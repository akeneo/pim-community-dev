<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

final class AxisRateCollectionSpec extends ObjectBehavior
{
    public function it_adds_rates_per_channel_and_locale()
    {
        $rateCollection1 = (new CriterionRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(80))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(40))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(75))
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));

        $rateCollection2 = (new CriterionRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(90))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(60))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(60))
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(80));

        $this
            ->addCriterionRateCollection($rateCollection1)
            ->addCriterionRateCollection($rateCollection2);

        $this->toArrayString()->shouldReturn([
            'ecommerce' => [
                'en_US' => 'B',
                'fr_FR' => 'E',
            ],
            'print' => [
                'en_US' => 'D',
                'fr_FR' => 'C',
            ],
        ]);
    }

    public function it_formats_for_consolidation()
    {
        $rateCollection1 = (new CriterionRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(80))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(40))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(75))
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));

        $rateCollection2 = (new CriterionRateCollection())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(90))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(60))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(60))
            ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(80));

        $this
            ->addCriterionRateCollection($rateCollection1)
            ->addCriterionRateCollection($rateCollection2);

        $this->formatForConsolidation()->shouldReturn([
            'ecommerce' => [
                'en_US' =>  ['rank' => 2, 'value' => 85],
                'fr_FR' => ['rank' => 5, 'value' => 50],
            ],
            'print' => [
                'en_US' => ['rank' => 4, 'value' => 67],
                'fr_FR' => ['rank' => 3, 'value' => 72],
            ],
        ]);
    }
}
