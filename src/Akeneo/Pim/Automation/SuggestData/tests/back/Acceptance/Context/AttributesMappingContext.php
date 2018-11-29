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
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FamilyCode;
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

    /** @var SearchFamiliesHandler */
    private $searchFamiliesHandler;

    /** @var array */
    private $retrievedFamilies;

    /** @var array */
    private $retrievedAttributesMapping;

    /**
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler
     * @param UpdateAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler
     * @param SearchFamiliesHandler $searchFamiliesHandler
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        UpdateAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->updateAttributesMappingByFamilyHandler = $updateAttributesMappingByFamilyHandler;
        $this->searchFamiliesHandler = $searchFamiliesHandler;

        $this->retrievedFamilies = [];
    }

    /**
     * @Then the retrieved attributes mapping for the family :familyCode should be:
     *
     * @param string $familyCode
     * @param TableNode $expectedAttributes
     */
    public function theRetrievedAttributesMappingShouldBe(string $familyCode, TableNode $expectedAttributes): void
    {
        $attributesMapping = [];
        foreach ($this->retrievedAttributesMapping as $attribute) {
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
    public function theAttributesAreMappedForTheFamilyAsFollows(string $familyCode, TableNode $mappings): void
    {
        $requestMapping = [];
        foreach ($mappings->getColumnsHash() as $mapping) {
            $requestMapping[$mapping['target_attribute_code']] = [
                'franklinAttribute' => [
                    'label' => 'A label',
                    'type' => 'text',
                ],
                'attribute' => $mapping['pim_attribute_code'],
                'status' => (int) $this->getStatusMapping()[$mapping['status']],
            ];
        }

        $command = new UpdateAttributesMappingByFamilyCommand($familyCode, $requestMapping);
        $this->updateAttributesMappingByFamilyHandler->handle($command);
    }

    /**
     * @When I search for all the families
     */
    public function iRetrieveTheFamilies(): void
    {
        $this->retrievedFamilies = $this->searchFamiliesHandler->handle(new SearchFamiliesQuery(20, 0, [], null));
    }

    /**
     * @param $familyCodeOrLabel
     *
     * @When I search a family with the query :familyCodeOrLabel
     */
    public function iSearchOneFamilyWithTheQuery(string $familyCodeOrLabel): void
    {
        $this->retrievedFamilies = $this->searchFamiliesHandler->handle(
            new SearchFamiliesQuery(
                20,
                0,
                [],
                $familyCodeOrLabel
            )
        );
    }

    /**
     * @When I retrieves the attributes mapping for the family :familyCode
     *
     * @param mixed $familyCode
     */
    public function iRetrievesTheAttributesMappingForTheFamily($familyCode): void
    {
        $query = new GetAttributesMappingByFamilyQuery($familyCode);
        $this->retrievedAttributesMapping = $this->getAttributesMappingByFamilyHandler->handle($query);
    }

    /**
     * @param string $families
     *
     * @Then /^I should have the famil(?:y|ies) (.*)$/
     */
    public function iShouldHaveTheFamilies(string $families): void
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

    /**
     * @param TableNode $expectedAttributes
     *
     * @return array|TableNode
     */
    private function buildExpectedAttributesMapping(TableNode $expectedAttributes)
    {
        $statusMapping = $this->getStatusMapping();

        $expectedAttributes = $expectedAttributes->getColumnsHash();
        foreach ($expectedAttributes as $index => $attribute) {
            $expectedAttributes[$index]['status'] = $statusMapping[$attribute['status']];
        }

        return $expectedAttributes;
    }

    /**
     * @return array
     */
    private function getStatusMapping(): array
    {
        return [
            'pending' => AttributeMapping::ATTRIBUTE_PENDING,
            'active' => AttributeMapping::ATTRIBUTE_MAPPED,
            'inactive' => AttributeMapping::ATTRIBUTE_UNMAPPED,
        ];
    }
}
