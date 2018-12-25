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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * Normalizes a PIM family to Franklin array format (for subscription purpose for example).
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FamilyNormalizer
{
    public function normalize(FamilyInterface $family): array
    {
        $familyLabels = [];
        foreach ($family->getTranslations() as $translation) {
            $familyLabels[$translation->getLocale()] = $translation->getLabel();
        }

        return [
            'code' => $family->getCode(),
            'label' => $familyLabels,
        ];
    }
}
