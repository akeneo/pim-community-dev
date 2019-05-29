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
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingWithSuggestionsHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingWithSuggestionsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\SearchFamiliesQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\CreateAttributeInFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository;
use Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Query\InMemorySelectExactMatchAttributeCodeQuery;
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

    /** @var CreateAttributeInFamilyHandler */
    private $createAttributeInFamilyHandler;

    /** @var FakeClient */
    private $fakeClient;

    /** @var array */
    private $retrievedFamilies;

    /** @var AttributesMappingResponse */
    private $retrievedAttributesMapping;

    /** @var array */
    private $originalAttributesMapping;

    /** @var GetAttributesMappingWithSuggestionsHandler */
    private $getAttributesMappingWithSuggestionsHandler;

    /** @var InMemorySelectExactMatchAttributeCodeQuery */
    private $inMemorySelectExactMatchAttributeCodeQuery;

    /** @var InMemoryFamilyRepository */
    private $familyRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingByFamilyHandler,
        SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler,
        SearchFamiliesHandler $searchFamiliesHandler,
        CreateAttributeInFamilyHandler $createAttributeInFamilyHandler,
        FakeClient $fakeClient,
        GetAttributesMappingWithSuggestionsHandler $getAttributesMappingWithSuggestionsHandler,
        InMemorySelectExactMatchAttributeCodeQuery $inMemorySelectExactMatchAttributeCodeQuery,
        InMemoryFamilyRepository $familyRepository,
        InMemoryAttributeRepository $attributeRepository
    ) {
        $this->getAttributesMappingByFamilyHandler = $getAttributesMappingByFamilyHandler;
        $this->saveAttributesMappingByFamilyHandler = $saveAttributesMappingByFamilyHandler;
        $this->searchFamiliesHandler = $searchFamiliesHandler;
        $this->createAttributeInFamilyHandler = $createAttributeInFamilyHandler;
        $this->fakeClient = $fakeClient;
        $this->getAttributesMappingWithSuggestionsHandler = $getAttributesMappingWithSuggestionsHandler;
        $this->inMemorySelectExactMatchAttributeCodeQuery = $inMemorySelectExactMatchAttributeCodeQuery;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;

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
     * @When I retrieve the attributes mapping with suggestions for the family :familyCode
     *
     * @param string $familyCode
     */
    public function iRetrieveTheAttributesMappingWithSuggestionsForTheFamily($familyCode): void
    {
        $query = new GetAttributesMappingWithSuggestionsQuery(new FamilyCode($familyCode));
        $this->retrievedAttributesMapping = $this->getAttributesMappingWithSuggestionsHandler->handle($query);
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
     * @When I create the :franklinAttrType attribute :franklinAttrLabel in the family :familyCode
     */
    public function iCreateTheAttributeInTheFamily($franklinAttrType, $franklinAttrLabel, $familyCode): void
    {
        try {
            $command = new CreateAttributeInFamilyCommand(
                new FamilyCode($familyCode),
                AttributeCode::fromLabel($franklinAttrLabel),
                new FranklinAttributeLabel($franklinAttrLabel),
                new FranklinAttributeType($franklinAttrType)
            );
            $this->createAttributeInFamilyHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
            var_dump($e->getMessage());
        }
    }

    /**
     * @Then the attribute :attrcode should not be created
     */
    public function theAttributeShouldNotBeCreated($attrCode): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attrCode);
        Assert::null($attribute);
    }

    /**
     * @Then the attribute :attrCode should belongs to the :attrGroupCode attribute group
     */
    public function theAttributeShouldBelongsToTheAttributeGroup($attrCode, $attrGroupCode): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attrCode);
        Assert::isInstanceOf($attribute, AttributeInterface::class);
        Assert::isInstanceOf($attribute->getGroup(), AttributeGroupInterface::class);
    }

    /**
     * @Then the family :familyCode should have the :franklinAttrType attribute :attrCode
     */
    public function theFamilyShouldHaveTheAttribute($familyCode, $franklinAttrType, $attrCode): void
    {
        $e = ExceptionContext::getThrownException();
        Assert::null($e);

        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        Assert::isInstanceOf($family, FamilyInterface::class);
        Assert::true($family->hasAttributeCode($attrCode));

        $familyAttributes = $family->getAttributes();
        foreach ($familyAttributes as $familyAttribute) {
            if ($familyAttribute->getCode() === $attrCode) {
                Assert::eq(
                    array_search($franklinAttrType, AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS),
                    $familyAttribute->getType()
                );
            }
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

        Assert::same(iterator_count($this->retrievedAttributesMapping->getIterator()), count($expectedAttributes));

        foreach ($expectedAttributes as $expectedAttribute) {
            $found = false;
            foreach ($this->retrievedAttributesMapping as $attributeMapping) {
                if ($expectedAttribute['target_attribute_code'] === $attributeMapping->getTargetAttributeCode()) {
                    $found = true;
                    Assert::eq($attributeMapping->getTargetAttributeCode(), $expectedAttribute['target_attribute_code']);
                    Assert::eq($attributeMapping->getTargetAttributeLabel(), $expectedAttribute['target_attribute_label']);
                    Assert::eq($attributeMapping->getTargetAttributeType(), $expectedAttribute['target_attribute_type']);
                    Assert::eq($attributeMapping->getPimAttributeCode(), $expectedAttribute['pim_attribute_code']);
                    Assert::eq($attributeMapping->getStatus(), $this->getAttributeMappingStatus($expectedAttribute['status']));
                    break;
                }
            }
            if (false === $found) {
                throw new \Exception('attribute not found');
            }
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
     * @Then a not supported metric type message should be sent
     */
    public function aNotSupportedMetricTypeMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::isInstanceOf($thrownException, \InvalidArgumentException::class);
        Assert::eq(
            $thrownException->getMessage(),
            'Can not create attribute. Attribute of type "metric" is not allowed'
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
