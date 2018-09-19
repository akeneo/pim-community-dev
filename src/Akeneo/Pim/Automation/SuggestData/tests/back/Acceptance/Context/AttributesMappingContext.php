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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Acceptance\Context;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributeMapping;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class AttributesMappingContext implements Context
{
    /** @var GetAttributesMappingByFamilyHandler */
    private $attributesMappingByFamilyHandler;

    /**
     * @param GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler
     */
    public function __construct(GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler)
    {
        $this->attributesMappingByFamilyHandler = $attributesMappingByFamilyHandler;
    }

    /**
     * @Then the retrieved attributes mapping for the family :familyCode should be:
     *
     * @param           $familyCode
     * @param TableNode $expectedAttributes
     */
    public function theRetrievedAttributesMappingShouldBe($familyCode, TableNode $expectedAttributes): void
    {
        $query = new GetAttributesMappingByFamilyQuery($familyCode);
        $attributesMappingResponse = $this->attributesMappingByFamilyHandler->handle($query);

        $attributesMapping = [];
        foreach ($attributesMappingResponse as $attribute) {
            $attributesMapping[] = [
                'target_attribute_code' => $attribute->getTargetAttributeCode(),
                'target_attribute_label' => $attribute->getTargetAttributeLabel(),
                'pim_attribute_code' => $attribute->getPimAttributeCode(),
                'status' => $attribute->getStatus(),
            ];
        }

        Assert::eq($this->buildExpectedAttributesMapping($expectedAttributes), $attributesMapping);
    }

    private function buildExpectedAttributesMapping(TableNode $expectedAttributes)
    {
        $statusMapping = [
            'pending' => AttributeMapping::ATTRIBUTE_PENDING,
            'active' => AttributeMapping::ATTRIBUTE_MAPPED,
            'inactive' => AttributeMapping::ATTRIBUTE_UNMAPPED,
        ];

        $expectedAttributes = $expectedAttributes->getColumnsHash();
        foreach ($expectedAttributes as $index => $attribute) {
            $expectedAttributes[$index]['status'] = $statusMapping[$attribute['status']];
        }

        return $expectedAttributes;
    }
}
