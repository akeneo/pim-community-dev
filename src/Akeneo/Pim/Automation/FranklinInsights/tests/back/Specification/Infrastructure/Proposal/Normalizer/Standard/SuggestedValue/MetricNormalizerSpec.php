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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\MetricNormalizer;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
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
            '/../../../../../../../../../../../../../' .
            'vendor/akeneo/pim-community-dev/src/Akeneo/Tool/Bundle/MeasureBundle/Resources/config/measure.yml';

        $configuration = Yaml::parse(file_get_contents($configurationFile));

        $measureConverter = new MeasureConverter($configuration);
        $this->beConstructedWith($attributeRepository, $measureConverter);
    }

    public function it_is_a_suggested_value_metric_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(MetricNormalizer::class);
    }

    public function it_normalizes_a_metric_suggested_value_with_integer_amount($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(false)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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

    public function it_normalizes_a_metric_suggested_value_with_floating_amount($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '4.2 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(true)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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
        $attributeRepository
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '4.2 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(false)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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

    public function it_normalizes_a_negative_metric_suggested_value_with_integer_amount($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '-42 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(false)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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

    public function it_normalizes_a_negative_metric_suggested_value_with_floating_amount($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '-4.2 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(true)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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
        $attributeRepository
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '-4.2 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(false)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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
        $attributeRepository
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+42 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(false)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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
        $attributeRepository
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+4.2 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(true)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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
        $attributeRepository
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+4.2 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(false)->withDefaultMetricUnit('CENTIMETER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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

    public function it_normalizes_a_metric_suggested_value_to_the_default_attribute_metric_unit($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(true)->withDefaultMetricUnit('METER')->withMetricFamily('Length')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

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

    public function it_returns_an_empty_array_if_the_metric_family_does_not_exist($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '42');

        $attribute = (new AttributeBuilder())->decimalsAllowed(false)->withMetricFamily('foobar')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

        $this->normalize($suggestedValue)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_metric_family_does_not_correspond_to_the_data($attributeRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attribute = (new AttributeBuilder())->decimalsAllowed(true)->withDefaultMetricUnit('HERTZ')->withMetricFamily('Frequency')->build();
        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);

        $this->normalize($suggestedValue)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_attribute_does_not_exist($attributeRepository): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42 CENTIMETER');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn(null);

        $this->normalize($suggestedValue)->shouldReturn([]);
    }
}
