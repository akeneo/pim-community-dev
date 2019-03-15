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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * Identifier Mapping doctrine entity.
 */
class IdentifierMapping
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $franklinCode;

    /** @var string|null */
    private $attributeCode;

    public function __construct(string $franklinCode, ?string $attributeCode)
    {
        $this->franklinCode = $franklinCode;
        $this->attributeCode = $attributeCode;
    }

    public function getFranklinCode(): string
    {
        return $this->franklinCode;
    }

    public function getAttributeCode(): ?AttributeCode
    {
        return !empty($this->attributeCode) ? new AttributeCode($this->attributeCode) : null;
    }
}
