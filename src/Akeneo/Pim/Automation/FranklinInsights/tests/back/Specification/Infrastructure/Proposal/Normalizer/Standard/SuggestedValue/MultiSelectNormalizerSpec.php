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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Query\SelectAttributeOptionCodesByIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\MultiSelectNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 *
 * @TODO These tests should break when pulling-up to 3.1, ask <paul.chasle@akeneo.com> for information.
 */
class MultiSelectNormalizerSpec extends ObjectBehavior
{
    public function let(SelectAttributeOptionCodesByIdentifiersQueryInterface $selectAttributeOptionCodesByIdentifiersQuery): void
    {
        $this->beConstructedWith($selectAttributeOptionCodesByIdentifiersQuery);
    }

    public function it_is_a_suggested_value_multi_select_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(MultiSelectNormalizer::class);
    }

    public function it_normalizes_a_multi_select_suggested_value($selectAttributeOptionCodesByIdentifiersQuery): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', ['attribute_option']);

        $selectAttributeOptionCodesByIdentifiersQuery
            ->execute('attribute_code', ['attribute_option'])
            ->willReturn(['attribute_option']);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => ['attribute_option'],
            ]],
        ]);
    }

    public function it_normalizes_a_string_multi_select_suggested_value($selectAttributeOptionCodesByIdentifiersQuery): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', 'attribute_option');

        $selectAttributeOptionCodesByIdentifiersQuery
            ->execute('attribute_code', ['attribute_option'])
            ->willReturn(['attribute_option']);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => ['attribute_option'],
            ]],
        ]);
    }

    public function it_keeps_only_existing_options_of_a_multi_select_suggested_value($selectAttributeOptionCodesByIdentifiersQuery): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', [
            'existing_attribute_option',
            'non_existing_attribute_option',
        ]);

        $selectAttributeOptionCodesByIdentifiersQuery
            ->execute('attribute_code', ['existing_attribute_option', 'non_existing_attribute_option'])
            ->willReturn(['existing_attribute_option']);

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => ['existing_attribute_option'],
            ]],
        ]);
    }

    public function it_returns_an_empty_array_if_no_options_exist($selectAttributeOptionCodesByIdentifiersQuery): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', [
            'an_non_existing_attribute_option',
            'another_non_existing_attribute_option',
        ]);

        $selectAttributeOptionCodesByIdentifiersQuery
            ->execute('attribute_code', [
                'an_non_existing_attribute_option',
                'another_non_existing_attribute_option',
            ])
            ->willReturn([]);

        $this->normalize($suggestedValue)->shouldReturn([]);
    }
}
