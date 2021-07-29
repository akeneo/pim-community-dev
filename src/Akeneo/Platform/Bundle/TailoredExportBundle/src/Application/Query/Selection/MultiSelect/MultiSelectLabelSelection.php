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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\MultiSelect;

final class MultiSelectLabelSelection implements MultiSelectSelectionInterface
{
    public const TYPE = 'label';

    private string $separator;
    private string $locale;
    private string $attributeCode;

    public function __construct(
        string $separator,
        string $locale,
        string $attributeCode
    ) {
        $this->separator = $separator;
        $this->locale = $locale;
        $this->attributeCode = $attributeCode;
    }

    public function getSeparator(): string
    {
        return $this->separator;
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
