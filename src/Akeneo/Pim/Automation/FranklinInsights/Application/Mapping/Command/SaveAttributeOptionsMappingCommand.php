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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SaveAttributeOptionsMappingCommand
{
    /** @var FamilyCode */
    private $familyCode;

    /** @var AttributeCode */
    private $attributeCode;

    /** @var FranklinAttributeId */
    private $franklinAttributeId;

    /** @var AttributeOptions */
    private $attributeOptions;

    /**
     * @param FamilyCode $familyCode
     * @param AttributeCode $attributeCode
     * @param FranklinAttributeId $franklinAttributeId
     * @param AttributeOptions $attributeOptions
     */
    public function __construct(
        FamilyCode $familyCode,
        AttributeCode $attributeCode,
        FranklinAttributeId $franklinAttributeId,
        AttributeOptions $attributeOptions
    ) {
        $this->familyCode = $familyCode;
        $this->attributeCode = $attributeCode;
        $this->franklinAttributeId = $franklinAttributeId;
        $this->attributeOptions = $attributeOptions;
    }

    /**
     * @return FamilyCode
     */
    public function familyCode(): FamilyCode
    {
        return $this->familyCode;
    }

    /**
     * @return AttributeCode
     */
    public function attributeCode(): AttributeCode
    {
        return $this->attributeCode;
    }

    /**
     * @return FranklinAttributeId
     */
    public function franklinAttributeId(): FranklinAttributeId
    {
        return $this->franklinAttributeId;
    }

    /**
     * @return AttributeOptions
     */
    public function attributeOptions(): AttributeOptions
    {
        return $this->attributeOptions;
    }
}
