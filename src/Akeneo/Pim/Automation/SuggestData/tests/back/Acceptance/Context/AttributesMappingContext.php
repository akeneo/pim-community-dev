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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
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
    private $getAttributesMappingByFamilyHandler;

    /** @var UpdateAttributesMappingByFamilyHandler */
    private $updateAttributesMappingByFamilyHandler;

    /**
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler
     * @param UpdateAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        UpdateAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->updateAttributesMappingByFamilyHandler = $updateAttributesMappingByFamilyHandler;
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
        $attributesMappingResponse = $this->getAttributesMappingByFamilyHandler->handle($query);

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

    /**
     * @When the attributes are mapped for the family :familyCode as follows:
     *
     * @param string $familyCode
     * @param TableNode $mappings
     */
    public function theAttributesAreMappedForTheFamilyAsFollows($familyCode, TableNode $mappings): void
    {
        $requestMapping = [];
        foreach ($mappings->getColumnsHash() as $mapping) {
            $requestMapping[$mapping['target_attribute_code']] = [
                'pim_ai_attribute' => [
                    'label' => 'A label',
                    'type' => 'text'
                ],
                'attribute' => $mapping['pim_attribute_code'],
                'status' => (int) $mapping['status']
            ];
        }

        $command = new UpdateAttributesMappingByFamilyCommand($familyCode, $requestMapping);
        $this->updateAttributesMappingByFamilyHandler->handle($command);
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
