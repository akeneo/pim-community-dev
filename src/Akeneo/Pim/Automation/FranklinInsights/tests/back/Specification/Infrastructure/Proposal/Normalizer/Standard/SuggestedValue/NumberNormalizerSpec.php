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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\NumberNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NumberNormalizerSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $attributeRepository): void
    {
        $this->beConstructedWith($attributeRepository);
    }

    public function it_is_a_suggested_value_number_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(NumberNormalizer::class);
    }

    public function it_normalizes_a_integer_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 42,
            ]],
        ]);
    }

    public function it_normalizes_a_integer_suggested_value_even_for_a_floating_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '6.66');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 6,
            ]],
        ]);
    }

    public function it_normalizes_a_floating_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '6.66');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 6.66,
            ]],
        ]);
    }

    public function it_normalizes_a_negative_integer_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '-42');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => -42,
            ]],
        ]);
    }

    public function it_normalizes_a_negative_integer_suggested_value_even_for_a_floating_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '-6.66');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => -6,
            ]],
        ]);
    }

    public function it_normalizes_a_negative_floating_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '-6.66');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => -6.66,
            ]],
        ]);
    }

    public function it_normalizes_an_explicitly_positive_integer_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+42');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 42,
            ]],
        ]);
    }

    public function it_normalizes_an_explicitly_positive_integer_suggested_value_even_for_a_floating_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+6.66');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 6,
            ]],
        ]);
    }

    public function it_normalizes_an_explicitly_positive_floating_suggested_value(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '+6.66');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 6.66,
            ]],
        ]);
    }

    public function it_returns_an_empty_array_if_the_suggested_value_is_a_text(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', 'foobar');

        $attributeRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $attribute->isDecimalsAllowed()->shouldNotBeCalled();

        $this->normalize($suggestedValue)->shouldReturn([]);
    }

    public function it_returns_an_empty_array_if_the_the_suggested_data_attribute_code_does_not_exist(
        $attributeRepository,
        AttributeInterface $attribute
    ): void {
        $suggestedValue = new SuggestedValue('attribute_code', '42');

        $attributeRepository->findOneByIdentifier('attribute_code')->willReturn(null);
        $attribute->isDecimalsAllowed()->shouldNotBeCalled();

        $this->normalize($suggestedValue)->shouldReturn([]);
    }
}
