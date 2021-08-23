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

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use Akeneo\Platform\TailoredExport\Domain\Query\FindFamilyLabelInterface;

class FindFamilyLabel implements FindFamilyLabelInterface
{
    private GetFamilyTranslations $getFamilyTranslations;

    public function __construct(GetFamilyTranslations $getFamilyTranslations)
    {
        $this->getFamilyTranslations = $getFamilyTranslations;
    }

    public function byCode(string $familyCode, string $locale): ?string
    {
        $translations = $this->getFamilyTranslations->byFamilyCodesAndLocale([$familyCode], $locale);

        return $translations[$familyCode] ?? null;
    }
}
