<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue\MetricNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MetricNormalizerSpec extends ObjectBehavior
{
    public function let(
        AttributeRepositoryInterface $attributeRepository
    ): void {
        $configurationFile = __DIR__ .
            '/../../../../../../../../../../../../' .
            'vendor/akeneo/pim-community-dev/src/Akeneo/Tool/Bundle/MeasureBundle/Resources/config/measure.yml';

        $configuration = Yaml::parse(file_get_contents($configurationFile));

        $measureConverter = new MeasureConverter($configuration);
        $this->beConstructedWith($attributeRepository, $measureConverter);
    }

    public function it_is_a_suggested_value_metric_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(MetricNormalizer::class);
    }

    public function it_normalizes_a_metric_suggested_value_with_integer_amount(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => 42,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_a_metric_suggested_value_with_floating_amount(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '4.2 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => 4.2,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_a_metric_suggested_value_with_floating_amount_even_if_decimal_is_not_allowed(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '4.2 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => 4,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_a_negative_metric_suggested_value_with_integer_amount(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '-42 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => -42,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_a_negative_metric_suggested_value_with_floating_amount(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '-4.2 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => -4.2,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_a_negative_metric_suggested_value_with_floating_amount_event_if_decimal_is_not_allowed(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '-4.2 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => -4,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_an_explicitly_positive_metric_suggested_value_with_integer_amount(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+42 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => 42,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_an_explicitly_positive_metric_suggested_value_with_floating_amount(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+4.2 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => 4.2,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_an_explicitly_positive_metric_suggested_value_with_floating_amount_event_if_decimal_is_not_allowed(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+4.2 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => 4,
                    'unit' => 'CENTIMETER',
                ],
            ]],
        ]);
    }

    public function it_normalizes_a_metric_suggested_value_to_the_default_attribute_metric_unit(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->getDefaultMetricUnit()->willReturn('METER');
        $attribute->getMetricFamily()->willReturn('Length');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => [
                    'amount' => 0.42,
                    'unit' => 'METER',
                ],
            ]],
        ]);
    }

    public function it_returns_an_empty_array_if_there_is_no_amount($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', 'Forty two CENTIMETER');

        $attributeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $this->normalize($suggestedValue)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_there_is_no_unit($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '42');

        $attributeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $this->normalize($suggestedValue)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_metric_family_does_not_exist(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->getMetricFamily()->willReturn('foobar');
        $attribute->getDefaultMetricUnit()->shouldNotBeCalled();
        $attribute->isDecimalsAllowed()->shouldNotBeCalled();

        $this->normalize($suggestedValue)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_metric_family_does_not_correspond_to_the_data(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->getMetricFamily()->willReturn('Frequency');
        $attribute->getDefaultMetricUnit()->willReturn('HERTZ');
        $attribute->isDecimalsAllowed()->shouldNotBeCalled();

        $this->normalize($suggestedValue)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_attribute_does_not_exist(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn(null);
        $attribute->getMetricFamily()->shouldNotBeCalled();
        $attribute->getDefaultMetricUnit()->shouldNotBeCalled();
        $attribute->isDecimalsAllowed()->shouldNotBeCalled();

        $this->normalize($suggestedValue)->shouldReturn([]);
    }
}
