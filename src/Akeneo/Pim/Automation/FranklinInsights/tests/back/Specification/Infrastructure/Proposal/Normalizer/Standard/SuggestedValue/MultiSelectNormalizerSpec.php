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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\MultiSelectNormalizer;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MultiSelectNormalizerSpec extends ObjectBehavior
{
    public function let(AttributeOptionRepositoryInterface $attributeOptionRepository): void
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    public function it_is_a_suggested_value_multi_select_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(MultiSelectNormalizer::class);
    }

    public function it_normalizes_a_multi_select_suggested_value($attributeOptionRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', ['attribute_option']);

        $attributeOptionRepository
            ->findCodesByIdentifiers('attribute_code', ['attribute_option'])
            ->willReturn(['attribute_option']);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => ['attribute_option'],
            ]],
        ]);
    }

    public function it_normalizes_a_string_multi_select_suggested_value($attributeOptionRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', 'attribute_option');

        $attributeOptionRepository
            ->findCodesByIdentifiers('attribute_code', ['attribute_option'])
            ->willReturn(['attribute_option']);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => ['attribute_option'],
            ]],
        ]);
    }

    public function it_keeps_only_existing_options_of_a_multi_select_suggested_value($attributeOptionRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', [
            'exiting_attribute_option',
            'non_existing_attribute_option',
        ]);

        $attributeOptionRepository
            ->findCodesByIdentifiers('attribute_code', ['exiting_attribute_option', 'non_existing_attribute_option'])
            ->willReturn(['exiting_attribute_option']);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => ['exiting_attribute_option'],
            ]],
        ]);
    }

    public function it_returns_an_empty_array_if_no_options_exist($attributeOptionRepository): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', [
            'an_non_exiting_attribute_option',
            'another_non_existing_attribute_option',
        ]);

        $attributeOptionRepository
            ->findCodesByIdentifiers('attribute_code', [
                'an_non_exiting_attribute_option',
                'another_non_existing_attribute_option',
            ])
            ->willReturn([]);

        $this->normalize($suggestedValue)->shouldReturn([]);
    }
}
