<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class FranklinAttributeCreated
{
    private $attributeCode;
    private $attributeType;

    public function __construct(AttributeCode $attributeCode, AttributeType $attributeType)
    {
        $this->attributeCode = $attributeCode;
        $this->attributeType = $attributeType;
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }

    public function getAttributeType(): AttributeType
    {
        return $this->attributeType;
    }
}
