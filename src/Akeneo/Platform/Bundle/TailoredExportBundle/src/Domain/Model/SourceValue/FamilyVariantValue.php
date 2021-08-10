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

namespace Akeneo\Platform\TailoredExport\Domain\Model\SourceValue;

class FamilyVariantValue implements SourceValueInterface
{
    private string $familyVariantCode;

    public function __construct(string $familyVariantCode)
    {
        $this->familyVariantCode = $familyVariantCode;
    }

    public function getFamilyVariantCode(): string
    {
        return $this->familyVariantCode;
    }
}
