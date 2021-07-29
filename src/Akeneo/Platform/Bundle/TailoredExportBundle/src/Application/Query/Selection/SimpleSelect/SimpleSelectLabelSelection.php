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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\SimpleSelect;

final class SimpleSelectLabelSelection implements SimpleSelectSelectionInterface
{
    public const TYPE = 'label';

    private string $locale;
    private string $attributeCode;

    public function __construct(
        string $locale,
        string $attributeCode
    ) {
        $this->locale = $locale;
        $this->attributeCode = $attributeCode;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
