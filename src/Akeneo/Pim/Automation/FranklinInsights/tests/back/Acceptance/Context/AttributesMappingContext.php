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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Context;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
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

    /** @var SaveAttributesMappingByFamilyHandler */
    private $saveAttributesMappingByFamilyHandler;

    /** @var SearchFamiliesHandler */
    private $searchFamiliesHandler;

    /** @var FakeClient */
    private $fakeClient;

    /** @var array */
    private $retrievedFamilies;

    /** @var AttributesMappingResponse */
    private $retrievedAttributesMapping;

    /** @var array */
    private $originalAttributesMapping;

    /**
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler
     * @param SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler
     * @param SearchFamiliesHandler $searchFamiliesHandler
     * @param FakeClient $fakeClient
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        FakeClient $fakeClient
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->saveAttributesMappingByFamilyHandler = $saveAttributesMappingByFamilyHandler;
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
        $requestedAttributesMapping = $this->extractPersistedAttributesMappingFromTable($table);

        $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode($familyCode), $requestedAttributesMapping);
        $this->saveAttributesMappingByFamilyHandler->handle($command);

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
        $requestedAttributesMapping = $this->extractPersistedAttributesMappingFromTable($table);

        try {
            $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode($familyCode), $requestedAttributesMapping);
            $this->saveAttributesMappingByFamilyHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
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
     * @param string $familyCode
     */
    public function iRetrieveTheAttributesMappingForTheFamily($familyCode): void
    {
        try {
            $query = new GetAttributesMappingByFamilyQuery(new FamilyCode($familyCode));
            $this->retrievedAttributesMapping = $this->getAttributesMappingByFamilyHandler->handle($query);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When the attributes mapping for the family :familyCode is updated with an empty mapping
     *
     * @param string $familyCode
     */
    public function theAttributesMappingIsUpdatedWithAnEmptyMapping(string $familyCode): void
    {
        try {
            $command = new SaveAttributesMappingByFamilyCommand(new FamilyCode($familyCode), []);
            $this->saveAttributesMappingByFamilyHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @Then the retrieved attributes mapping for the family :familyCode should be:
     *
     * @param string $familyCode
     * @param TableNode $table
     */
    public function theRetrievedAttributesMappingShouldBe(string $familyCode, TableNode $table): void
    {
        $expectedAttributes = $table->getHash();

        foreach ($this->retrievedAttributesMapping as $index => $attributeMapping) {
            /* @var AttributeMapping $attributeMapping */
            Assert::eq($attributeMapping->getTargetAttributeCode(), $expectedAttributes[$index]['target_attribute_code']);
            Assert::eq($attributeMapping->getTargetAttributeLabel(), $expectedAttributes[$index]['target_attribute_label']);
            Assert::eq($attributeMapping->getTargetAttributeType(), $expectedAttributes[$index]['target_attribute_type']);
            Assert::eq($attributeMapping->getPimAttributeCode(), $expectedAttributes[$index]['pim_attribute_code']);
            Assert::eq($attributeMapping->getStatus(), $this->getAttributeMappingStatus($expectedAttributes[$index]['status']));
        }
    }

    /**
     * @Then the retrieved attributes mapping should be empty
     */
    public function theRetrievedAttributesMappingShouldBeEmpty(): void
    {
        Assert::null(ExceptionContext::getThrownException());
        Assert::count($this->retrievedAttributesMapping->getIterator(), 0);
    }

    /**
     * @Then Franklin's attribute :franklinAttribute should not be mapped
     *
     * @param string $franklinAttribute
     */
    public function franklinsAttributeShouldNotBeMapped($franklinAttribute): void
    {
        $attributesMapping = $this->fakeClient->getAttributesMapping();
        foreach ($attributesMapping as $attributeMapping) {
            if ($franklinAttribute === $attributeMapping['from']['id']) {
                Assert::null($attributeMapping['to']);
                Assert::eq($attributeMapping['status'], 'pending');

                return;
            }
        }
        Assert::true(false, 'Expectation not found for Franklin\'s attribute: ' . $franklinAttribute);
    }

    /**
     * @Then Franklin's attribute :franklinAttribute should be mapped to :pimAttributeCode
     *
     * @param string $franklinAttribute
     * @param string $pimAttributeCode
     */
    public function franklinsAttributeShouldBeMappedTo($franklinAttribute, $pimAttributeCode): void
    {
        $attributesMapping = $this->fakeClient->getAttributesMapping();
        foreach ($attributesMapping as $attributeMapping) {
            if ($franklinAttribute === $attributeMapping['from']['id']) {
                Assert::eq($pimAttributeCode, $attributeMapping['to']['id']);
                Assert::eq('active', $attributeMapping['status']);

                return;
            }
        }
        Assert::true(false, 'Expectation not found for Franklin\'s attribute: ' . $franklinAttribute);
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
                if ((string) $retrievedFamily->getFamily()->getCode() === $familyCode) {
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

        Assert::isInstanceOf(ExceptionContext::getThrownException(), \Exception::class);
    }

    /**
     * @Then an empty attributes mapping message should be sent
     */
    public function anEmptyAttributesMappingMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, AttributeMappingException::class);
        Assert::eq($thrownException->getMessage(), AttributeMappingException::emptyAttributesMapping()->getMessage());
    }

    /**
     * @Then an invalid :attributeType attribute type mapping message should be sent
     *
     * @param string $attributeType
     */
    public function anInvalidAttributeTypeMappingMessageShouldBeSent($attributeType): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, AttributeMappingException::class);
        Assert::eq(
            $thrownException->getMessage(),
            AttributeMappingException::incompatibleAttributeTypeMapping($attributeType)->getMessage()
        );
    }

    /**
     * @Then an invalid localizable attribute message should be sent
     */
    public function anInvalidLocalizableAttributeMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, AttributeMappingException::class);
        Assert::eq(
            $thrownException->getMessage(),
            AttributeMappingException::localizableAttributeNotAllowed()->getMessage()
        );
    }

    /**
     * @Then an invalid scopable attribute message should be sent
     */
    public function anInvalidScopableAttributeMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, AttributeMappingException::class);
        Assert::eq(
            $thrownException->getMessage(),
            AttributeMappingException::scopableAttributeNotAllowed()->getMessage()
        );
    }

    /**
     * @Then an invalid locale specific attribute message should be sent
     */
    public function anInvalidLocaleSpecificAttributeMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, AttributeMappingException::class);
        Assert::eq(
            $thrownException->getMessage(),
            AttributeMappingException::localeSpecificAttributeNotAllowed()->getMessage()
        );
    }

    /**
     * @Then an invalid duplicated pim attribute message should be sent
     */
    public function anInvalidDuplicatedPimAttributeMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, AttributeMappingException::class);
        Assert::eq(
            $thrownException->getMessage(),
            AttributeMappingException::duplicatedPimAttribute()->getMessage()
        );
    }

    /**
     * @param mixed $franklinStatus
     *
     * @return int
     */
    private function getAttributeMappingStatus($franklinStatus): int
    {
        switch ($franklinStatus) {
            case 'pending':
                return AttributeMappingStatus::ATTRIBUTE_PENDING;
            case 'active':
                return AttributeMappingStatus::ATTRIBUTE_ACTIVE;
            case 'inactive':
                return AttributeMappingStatus::ATTRIBUTE_INACTIVE;
        }
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function extractPersistedAttributesMappingFromTable(TableNode $table): array
    {
        $requestedAttributesMapping = [];
        foreach ($table->getColumnsHash() as $mapping) {
            $requestedAttributesMapping[$mapping['target_attribute_code']] = [
                'franklinAttribute' => [
                    'label' => 'A label',
                    'type' => 'text',
                ],
                'attribute' => $mapping['pim_attribute_code'],
                'status' => 'pending',
            ];
        }

        return $requestedAttributesMapping;
    }
}
