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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;

/**
 * Identifier Mapping doctrine entity.
 */
class IdentifierMapping
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $franklinCode;

    /** @var Attribute|null */
    private $attribute;

    /** @var string|null */
    private $attributeCode;

    /**
     * @param string $franklinCode
     * @param Attribute|null $attribute
     */
    public function __construct(string $franklinCode, ?Attribute $attribute)
    {
        $this->franklinCode = $franklinCode;
        $this->attribute = $attribute;
        if ($attribute instanceof Attribute && !empty($attribute->getCode())) {
            $this->attributeCode = (string) $attribute->getCode();
        }
    }

    /**
     * @return string
     */
    public function getFranklinCode(): string
    {
        return $this->franklinCode;
    }

    /**
     * @return Attribute|null
     */
    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    /**
     * @return null|string
     */
    public function getAttributeCode(): ?string
    {
        return $this->attributeCode;
    }
}
