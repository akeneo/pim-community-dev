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
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\FamilyCollection;
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

    /** @var SearchFamiliesHandler */
    private $searchFamiliesHandler;

    /** @var array */
    private $retrievedFamilies;

    /**
     * @param GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler
     */
    public function __construct(GetAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler, SearchFamiliesHandler $searchFamiliesHandler)
    {
        $this->attributesMappingByFamilyHandler = $attributesMappingByFamilyHandler;
        $this->searchFamiliesHandler = $searchFamiliesHandler;

        $this->retrievedFamilies = [];
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

    /**
     * @When I search for all the families
     */
    public function iRetrieveTheFamilies()
    {
        $this->retrievedFamilies = $this->searchFamiliesHandler->handle(new SearchFamiliesQuery(20, 0, [], null));
    }

    /**
     * @param $familyCodeOrLabel
     *
     * @When I search a family with the query :familyCodeOrLabel
     */
    public function iSearchAFamilyWithTheQuery(string $familyCodeOrLabel)
    {
        $this->retrievedFamilies = $this->searchFamiliesHandler->handle(new SearchFamiliesQuery(20, 0, [], $familyCodeOrLabel));
    }

    /**
     * @param string $families
     *
     * @Then /^I should have the famil(?:y|ies) (.*)$/
     */
    public function iShouldHaveTheFamilies(string $families)
    {
        $expectedFamilyCodes = explode(', ', str_replace(' and ', ', ', $families));

        Assert::count($this->retrievedFamilies, count($expectedFamilyCodes));

        foreach ($expectedFamilyCodes as $familyCode) {
            $found = false;
            foreach ($this->retrievedFamilies as $retrievedFamily) {
                if ($retrievedFamily->getCode() === $familyCode) {
                    $found = true;
                }
            }
            Assert::true($found);
        }
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
