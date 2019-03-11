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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue\BooleanNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class BooleanNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_suggested_value_boolean_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(BooleanNormalizer::class);
    }

    public function it_normalizes_a_boolean_suggested_value_with_true_value(): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', 'Yes');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => true,
            ]],
        ]);
    }

    public function it_normalizes_a_boolean_suggested_value_with_false_value(): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', 'No');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => false,
            ]],
        ]);
    }

    public function it_returns_an_emtpy_array_if_data_is_not_boolean(): void
    {
        $textSuggestedValue = new SuggestedValue('attribute_code', 'foobar');
        $numberSuggestedValue = new SuggestedValue('attribute_code', '0');
        $arraySuggestedValue = new SuggestedValue('attribute_code', ['foobar']);

        $this->normalize($textSuggestedValue)->shouldReturn([]);
        $this->normalize($numberSuggestedValue)->shouldReturn([]);
        $this->normalize($arraySuggestedValue)->shouldReturn([]);
    }
}
