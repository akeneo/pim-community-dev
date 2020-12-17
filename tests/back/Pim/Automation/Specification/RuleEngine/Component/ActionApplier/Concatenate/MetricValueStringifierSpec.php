<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\MetricValueStringifier;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\GetUnitTranslations;
use PhpSpec\ObjectBehavior;

class MetricValueStringifierSpec extends ObjectBehavior
{
    function let(GetUnitTranslations $getUnitTranslations)
    {
        $this->beConstructedWith($getUnitTranslations, ['type1', 'type2']);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(MetricValueStringifier::class);
    }

    function it_implements_value_stringifier_interface()
    {
        $this->shouldBeAnInstanceOf(ValueStringifierInterface::class);
    }

    function it_returns_supported_attribute_types()
    {
        $this->forAttributesTypes()->shouldBe(['type1', 'type2']);
    }

    function it_stringifies_a_metric_value(MetricValue $value)
    {
        $value->getAmount()->willReturn('1.0000');
        $value->getUnit()->willReturn('m');

        $this->stringify($value)->shouldBe('1 m');
    }

    function it_stringifies_a_metric_value_with_decimal(MetricValue $value)
    {
        $value->getAmount()->willReturn('1.2500');
        $value->getUnit()->willReturn('m');
        $this->stringify($value)->shouldBe('1.25 m');

        $value->getAmount()->willReturn('1.6131344166');
        $value->getUnit()->willReturn('m');
        $this->stringify($value)->shouldBe('1.6131344166 m');
    }

    function it_stringifies_a_metric_value_with_an_unit_label_locale(
        GetUnitTranslations $getUnitTranslations,
        MetricValue $value,
        MetricInterface $metric
    ) {
        $metric->getFamily()->willReturn('familyCode');
        $value->getAmount()->willReturn('1.0000');
        $value->getUnit()->willReturn('m');
        $value->getData()->willReturn($metric);

        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('familyCode', 'en_US')->willReturn([
            'km' => 'kilometer',
            'm' => 'meter',
        ]);

        $this->stringify($value, ['unit_label_locale' => 'en_US'])->shouldBe('1 meter');
    }

    function it_stringifies_a_metric_value_with_an_unknown_unit_label_locale(
        GetUnitTranslations $getUnitTranslations,
        MetricValue $value,
        MetricInterface $metric
    ) {
        $metric->getFamily()->willReturn('familyCode');
        $value->getAmount()->willReturn('1.0000');
        $value->getUnit()->willReturn('m');
        $value->getData()->willReturn($metric);

        $getUnitTranslations->byMeasurementFamilyCodeAndLocale('familyCode', 'unknown')->willReturn([]);

        $this->stringify($value, ['unit_label_locale' => 'unknown'])->shouldBe('1 m');
    }

    function it_stringifies_a_metric_value_without_decimal_trailing_zeros(MetricValue $value)
    {
        $value->getAmount()->willReturn('1500.0000');
        $value->getUnit()->willReturn('m');
        $this->stringify($value)->shouldBe('1500 m');
    }

    function it_stringifies_a_metric_value_without_trailing_decimal_separator(MetricValue $value)
    {
        $value->getAmount()->willReturn('300.');
        $value->getUnit()->willReturn('m');
        $this->stringify($value)->shouldBe('300 m');
    }
}
