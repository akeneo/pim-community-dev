<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class AttributeOptionCode
{
    /** @var string */
    private $optionCode;

    /** @var AttributeCode */
    private $attributeCode;

    public function __construct(AttributeCode $attributeCode, string $optionCode)
    {
        if ('' === $optionCode) {
            throw new \InvalidArgumentException('An attribute option code cannot be empty');
        }

        $this->optionCode = $optionCode;
        $this->attributeCode = $attributeCode;
    }

    public function __toString()
    {
        return $this->optionCode;
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }
}
