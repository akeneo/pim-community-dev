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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class FranklinAttributeAddedToFamily
{
    private $attributeCode;
    private $familyCode;

    public function __construct(AttributeCode $attributeCode, FamilyCode $familyCode)
    {
        $this->attributeCode = $attributeCode;
        $this->familyCode = $familyCode;
    }

    public function getAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }

    public function getFamilyCode(): FamilyCode
    {
        return $this->familyCode;
    }
}
