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

use Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue\TextNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class TextNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_suggested_value_text_normalizer(): void
    {
        $this->shouldBeAnInstanceOf(TextNormalizer::class);
    }

    public function it_normalizes_a_text_suggested_value(): void
    {
        $suggestedValue = new SuggestedValue('attribute_code', 'a text value');

        $this->normalize($suggestedValue)->shouldReturn([
            'attribute_code' => [[
                'scope' => null,
                'locale' => null,
                'data' => 'a text value',
            ]],
        ]);
    }
}
