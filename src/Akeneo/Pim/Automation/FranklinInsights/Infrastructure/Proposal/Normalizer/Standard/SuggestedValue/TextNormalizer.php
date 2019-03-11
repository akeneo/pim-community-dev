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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;

/**
 * Normalizes a suggested value to Akeneo standard format for text, textarea, identifier and date attribute types.
 * All those Akeneo attribute types are simple text on Franklin side.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class TextNormalizer
{
    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    public function normalize(SuggestedValue $suggestedValue): array
    {
        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => $suggestedValue->value(),
            ]],
        ];
    }
}
