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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;

/**
 * Normalizes a suggested value to Akeneo standard format for a boolean attribute type.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class BooleanNormalizer
{
    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    public function normalize(SuggestedValue $suggestedValue): array
    {
        $pimStandardData = null;
        $value = $suggestedValue->value();

        if ('Yes' === $value) {
            $pimStandardData = true;
        } elseif ('No' === $value) {
            $pimStandardData = false;
        }

        if (null === $pimStandardData) {
            return [];
        }

        return [
            $suggestedValue->pimAttributeCode() => [[
                'scope' => null,
                'locale' => null,
                'data' => $pimStandardData,
            ]],
        ];
    }
}
