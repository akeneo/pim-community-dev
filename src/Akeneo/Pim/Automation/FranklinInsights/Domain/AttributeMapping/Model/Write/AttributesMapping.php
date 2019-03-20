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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributesMapping
{
    /** @var FamilyCode */
    private $familyCode;

    /** @var AttributeMapping[] */
    private $mapping = [];

    public function __construct(FamilyCode $familyCode)
    {
        $this->familyCode = $familyCode;
    }

    /**
     * @return FamilyCode
     */
    public function familyCode(): FamilyCode
    {
        return $this->familyCode;
    }

    /**
     * @return AttributeMapping[]
     */
    public function mapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param string $franklinAttrId
     * @param string $franklinAttrType
     * @param Attribute|null $pimAttribute
     *
     * @throws \Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException
     */
    public function map(string $franklinAttrId, string $franklinAttrType, ?Attribute $pimAttribute): void
    {
        $this->mapping[] = new AttributeMapping($franklinAttrId, $franklinAttrType, $pimAttribute);
    }
}
