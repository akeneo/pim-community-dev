<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Attribute
{
    public function __construct(
        private string $code,
        private string $label,
        private string $attributeGroupCode,
        private string $attributeGroupLabel,
    ) {
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
