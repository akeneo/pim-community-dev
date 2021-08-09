<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\Structure;

use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use Akeneo\Platform\TailoredExport\Domain\Query\FindFamilyVariantLabelInterface;

class FindFamilyVariantLabel implements FindFamilyVariantLabelInterface
{
    private GetFamilyVariantTranslations $getFamilyVariantTranslations;

    public function __construct(GetFamilyVariantTranslations $getFamilyVariantTranslations)
    {
        $this->getFamilyVariantTranslations = $getFamilyVariantTranslations;
    }

    public function byCode(string $familyVariantCode, string $locale): ?string
    {
        $translations = $this->getFamilyVariantTranslations->byFamilyVariantCodesAndLocale([$familyVariantCode], $locale);

        return $translations[$familyVariantCode] ?? null;
    }
}
