<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Query\Attribute;

class Attribute
{
    private string $code;
    private string $label;
    private string $attributeGroupCode;
    private string $attributeGroupLabel;

    public function __construct(string $code, string $label, string $attributeGroupCode, string $attributeGroupLabel)
    {
        $this->code = $code;
        $this->label = $label;
        $this->attributeGroupCode = $attributeGroupCode;
        $this->attributeGroupLabel = $attributeGroupLabel;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAttributeGroupCode(): string
    {
        return $this->attributeGroupCode;
    }

    public function getAttributeGroupLabel(): string
    {
        return $this->attributeGroupLabel;
    }
}
