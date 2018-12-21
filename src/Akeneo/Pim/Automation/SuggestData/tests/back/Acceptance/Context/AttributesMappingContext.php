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
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\FakeClient;
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

    /** @var FakeClient */
    private $fakeClient;

    /** @var array */
    private $retrievedFamilies;

    /** @var array */
    private $retrievedAttributesMapping;

    /** @var \Exception */
    private $thrownException;

    /** @var array */
    private $originalAttributesMapping;

    /**
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler
     * @param UpdateAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler
     * @param SearchFamiliesHandler $searchFamiliesHandler
     * @param FakeClient $fakeClient
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        UpdateAttributesMappingByFamilyHandler $updateAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FakeClient $fakeClient
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->updateAttributesMappingByFamilyHandler = $updateAttributesMappingByFamilyHandler;
        $this->searchFamiliesHandler = $searchFamiliesHandler;
        $this->fakeClient = $fakeClient;

        $this->originalAttributesMapping = null;
        $this->retrievedFamilies = [];
    }

    /**
     * @Given a predefined attributes mapping for the family :familyCode as follows:
     *
     * @param string $familyCode
     * @param TableNode $table
     */
    public function aPredefinedAttributesMapping(string $familyCode, TableNode $table): void
    {
        $requestAttributesMapping = $this->buildAttributesMappingRequest($table);
        $command = new UpdateAttributesMappingByFamilyCommand($familyCode, $requestAttributesMapping);
        $this->updateAttributesMappingByFamilyHandler->handle($command);

        $this->originalAttributesMapping = $this->fakeClient->getAttributesMapping();
    }

    /**
     * @When the attributes are mapped for the family :familyCode as follows:
     *
     * @param string $familyCode
     * @param TableNode $table
     */
    public function theAttributesAreMappedForTheFamilyAsFollows(string $familyCode, TableNode $table): void
    {
        $requestMapping = $this->buildAttributesMappingRequest($table);

        try {
            $command = new UpdateAttributesMappingByFamilyCommand($familyCode, $requestMapping);
            $this->updateAttributesMappingByFamilyHandler->handle($command);
        } catch (\Exception $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @When I search for all the families
     */
    public function iRetrieveTheFamilies(): void
    {
        $this->retrievedFamilies = $this->searchFamiliesHandler->handle(new SearchFamiliesQuery(20, 0, null));
    }

    /**
     * @param $familyCodeOrLabel
     *
     * @When I search a family with the query :familyCodeOrLabel
     */
    public function iSearchOneFamilyWithTheQuery(string $familyCodeOrLabel): void
    {
        $this->retrievedFamilies = $this->searchFamiliesHandler->handle(
            new SearchFamiliesQuery(20, 0, $familyCodeOrLabel)
        );
    }

    /**
     * @When I retrieve the attributes mapping for the family :familyCode
     *
     * @param mixed $familyCode
     */
    public function iRetrievesTheAttributesMappingForTheFamily($familyCode): void
    {
        $query = new GetAttributesMappingByFamilyQuery($familyCode);
        $this->retrievedAttributesMapping = $this->getAttributesMappingByFamilyHandler->handle($query);
    }

    /**
     * @When the attributes mapping for the family :familyCode is updated with an empty mapping
     *
     * @param string $familyCode
     */
    public function theAttributesMappingIsUpdatedWithAnEmptyMapping(string $familyCode): void
    {
        try {
            $command = new UpdateAttributesMappingByFamilyCommand($familyCode, []);
            $this->updateAttributesMappingByFamilyHandler->handle($command);
        } catch (\Exception $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @Then the retrieved attributes mapping for the family :familyCode should be:
     *
     * @param string $familyCode
     * @param TableNode $expectedAttributes
     */
    public function theRetrievedAttributesMappingShouldBe(string $familyCode, TableNode $expectedAttributes): void
    {
        $query = new GetAttributesMappingByFamilyQuery($familyCode);
        $attributesMappingResponse = $this->getAttributesMappingByFamilyHandler->handle($query);

        $attributesMapping = [];
        foreach ($attributesMappingResponse as $attribute) {
            $attributesMapping[] = [
                'target_attribute_code' => $attribute->getTargetAttributeCode(),
                'target_attribute_label' => $attribute->getTargetAttributeLabel(),
                'target_attribute_type' => $attribute->getTargetAttributeType(),
                'pim_attribute_code' => $attribute->getPimAttributeCode(),
                'status' => $attribute->getStatus(),
            ];
        }

        Assert::eq($this->buildExpectedAttributesMapping($expectedAttributes), $attributesMapping);
    }

    /**
     * @Then the attributes mapping should be saved as follows:
     *
     * @param TableNode $expectedMapping
     */
    public function theAttributesMappingShouldBeSavedAsFollows(TableNode $expectedMapping): void
    {
        $clientMapping = $this->fakeClient->getAttributesMapping();

        $this->assertAttributesMappingSentToFranklin($expectedMapping, $clientMapping);
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
     * @Then the attributes mapping should not be saved
     */
    public function theAttributesMappingShouldNotBeSaved(): void
    {
        $clientMapping = $this->fakeClient->getAttributesMapping();

        if (null !== $this->originalAttributesMapping) {
            Assert::eq($this->originalAttributesMapping, $clientMapping);
        } else {
            Assert::isEmpty($clientMapping);
        }

        Assert::isInstanceOf($this->thrownException, \Exception::class);
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

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function buildAttributesMappingRequest(TableNode $table): array
    {
        $requestAttributesMapping = [];
        foreach ($table->getColumnsHash() as $mapping) {
            $requestAttributesMapping[$mapping['target_attribute_code']] = [
                'franklinAttribute' => [
                    'label' => 'A label',
                    'type' => 'text',
                ],
                'attribute' => $mapping['pim_attribute_code'],
                'status' => (int) $this->getStatusMapping()[$mapping['status']],
            ];
        }

        return $requestAttributesMapping;
    }

    /**
     * @param TableNode $expectedMapping
     * @param $clientMapping
     */
    private function assertAttributesMappingSentToFranklin(TableNode $expectedMapping, $clientMapping): void
    {
        $statusMapping = $this->getStatusMapping();

        $attributesMapping = [];
        foreach ($clientMapping as $attribute) {
            $attributesMapping[] = [
                'target_attribute_code' => $attribute['from']['id'],
                'pim_attribute_code' => !empty($attribute['to']['id']) ? $attribute['to']['id'] : '',
                'pim_attribute_type' => $attribute['to']['type'] ?? '',
                'status' => $statusMapping[$attribute['status']],
            ];
        }

        Assert::eq($this->buildExpectedAttributesMapping($expectedMapping), $attributesMapping);
    }
}
