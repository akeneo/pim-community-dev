<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;


use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AddAttributeToFamilyCommand
{
    private $attributeCode;

    private $familyCode;

    public function __construct(AttributeCode $attributeCode, FamilyCode $familyCode)
    {
        $this->attributeCode = $attributeCode;
        $this->familyCode = $familyCode;
    }

    public function getPimAttributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }

    public function getPimFamilyCode(): FamilyCode
    {
        return $this->familyCode;
    }
}
