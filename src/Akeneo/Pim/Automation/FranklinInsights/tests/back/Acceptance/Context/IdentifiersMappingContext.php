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

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetIdentifiersMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobLauncher\InMemoryIdentifyProductsToResubscribe;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class IdentifiersMappingContext implements Context
{
    /** @var GetIdentifiersMappingHandler */
    private $getIdentifiersMappingHandler;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var SaveIdentifiersMappingHandler */
    private $saveIdentifiersMappingHandler;

    /** @var InMemoryIdentifyProductsToResubscribe */
    private $identifyProductsToResubscribe;

    /** @var FakeClient */
    private $fakeClient;

    /** @var array */
    private $originalIdentifiersMapping;

    /** @var IdentifiersMapping */
    private $retrievedIdentifiersMapping;

    /**
     * @param GetIdentifiersMappingHandler $getIdentifiersMappingHandler
     * @param SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FakeClient $fakeClient
     * @param InMemoryIdentifyProductsToResubscribe $identifyProductsToResubscribe
     */
    public function __construct(
        GetIdentifiersMappingHandler $getIdentifiersMappingHandler,
        SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        FakeClient $fakeClient,
        InMemoryIdentifyProductsToResubscribe $identifyProductsToResubscribe
    ) {
        $this->getIdentifiersMappingHandler = $getIdentifiersMappingHandler;
        $this->saveIdentifiersMappingHandler = $saveIdentifiersMappingHandler;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->attributeRepository = $attributeRepository;
        $this->fakeClient = $fakeClient;
        $this->identifyProductsToResubscribe = $identifyProductsToResubscribe;
    }

    /**
     * @Given an empty identifiers mapping
     */
    public function anEmptyIdentifiersMapping(): void
    {
        $this->assertIdentifiersMappingIsEmpty();
    }

    /**
     * @Given a predefined identifiers mapping as follows:
     *
     * @param TableNode $table
     *
     * @throws DataProviderException
     * @throws InvalidMappingException
     */
    public function aPredefinedIdentifiersMapping(TableNode $table): void
    {
        $this->originalIdentifiersMapping = $this->extractIdentifiersMappingFromTable($table);

        $command = new SaveIdentifiersMappingCommand($this->originalIdentifiersMapping);
        $this->saveIdentifiersMappingHandler->handle($command);
    }

    /**
     * @When the identifiers are mapped as follows:
     *
     * @param TableNode $table
     */
    public function theIdentifiersAreMappedAsFollows(TableNode $table): void
    {
        $identifiersMapping = $this->extractIdentifiersMappingFromTable($table);
        try {
            $command = new SaveIdentifiersMappingCommand($identifiersMapping);
            $this->saveIdentifiersMappingHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When the identifiers are mapped with empty values
     */
    public function theIdentifiersMappingIsSavedWithEmptyValues(): void
    {
        try {
            $command = new SaveIdentifiersMappingCommand([]);
            $this->saveIdentifiersMappingHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When I retrieve the identifiers mapping
     */
    public function iRetrieveTheIdentifiersMapping(): void
    {
        try {
            $query = new GetIdentifiersMappingQuery();
            $this->retrievedIdentifiersMapping = $this->getIdentifiersMappingHandler->handle($query);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @Then the identifiers mapping should not be saved
     */
    public function theIdentifiersMappingShouldNotBeSaved(): void
    {
        Assert::assertNotNull(ExceptionContext::getThrownException());

        if (null === $this->originalIdentifiersMapping) {
            $this->assertIdentifiersMappingIsEmpty();
        } else {
            $this->assertIdentifiersMappingPersisted($this->originalIdentifiersMapping);
            $this->assertIdentifiersMappingSentToFranklin($this->originalIdentifiersMapping);
        }
    }

    /**
     * @Then the identifiers mapping should be saved as follows:
     *
     * @param TableNode $table
     */
    public function theIdentifiersMappingShouldBeSavedAsFollows(TableNode $table): void
    {
        Assert::assertNull(ExceptionContext::getThrownException());

        $expectedIdentifiersMapping = $this->extractIdentifiersMappingFromTable($table);

        $this->assertIdentifiersMappingSentToFranklin($expectedIdentifiersMapping);
        $this->assertIdentifiersMappingPersisted($expectedIdentifiersMapping);
    }

    /**
     * @Then the retrieved identifiers mapping should be empty
     */
    public function theRetrievedIdentifiersMappingShouldBeEmpty(): void
    {
        Assert::assertNull(ExceptionContext::getThrownException());
        Assert::assertTrue($this->retrievedIdentifiersMapping->isEmpty());
    }

    /**
     * @Then the retrieved identifiers mapping should be:
     *
     * @param TableNode $table
     */
    public function theRetrievedIdentifiersMappingShouldBe(TableNode $table): void
    {
        Assert::assertNull(ExceptionContext::getThrownException());
        Assert::assertFalse($this->retrievedIdentifiersMapping->isEmpty());

        $expectedIdentifiersMapping = $this->extractIdentifiersMappingFromTable($table);
        foreach ($expectedIdentifiersMapping as $expectedIdentifier => $expectedMappedAttribute) {
            $mappedAttributeCode = $this->retrievedIdentifiersMapping->getMappedAttributeCode($expectedIdentifier);
            if (null === $expectedMappedAttribute) {
                Assert::assertNull($mappedAttributeCode);
            } else {
                Assert::assertEquals($expectedMappedAttribute, (string) $mappedAttributeCode);
            }
        }
    }

    /**
     * @Then an invalid mapping message should be sent
     */
    public function anInvalidMappingMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(\Exception::class, $thrownException);
        Assert::assertNotEmpty($thrownException->getMessage());
    }

    /**
     * @Then an invalid identifier :pimAttributeCode attribute type message should be sent
     *
     * @param string $pimAttributeCode
     */
    public function anInvalidIdentifierAttributeTypeMessageShouldBeSent(string $pimAttributeCode): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(InvalidMappingException::class, $thrownException);
        Assert::assertEquals(
            InvalidMappingException::attributeType('foo', $pimAttributeCode)->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid identifier :pimAttributeCode localizable message should be sent
     *
     * @param string $pimAttributeCode
     */
    public function anInvalidIdentifierLocalizableMessageShouldBeSent(string $pimAttributeCode): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(InvalidMappingException::class, $thrownException);
        Assert::assertEquals(
            InvalidMappingException::localizableAttributeNotAllowed($pimAttributeCode)->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid identifier :pimAttributeCode scopable message should be sent
     *
     * @param string $pimAttributeCode
     */
    public function anInvalidIdentifierScopableMessageShouldBeSent(string $pimAttributeCode): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(InvalidMappingException::class, $thrownException);
        Assert::assertEquals(
            InvalidMappingException::scopableAttributeNotAllowed($pimAttributeCode)->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid identifier :pimAttributeCode locale specific message should be sent
     *
     * @param string $pimAttributeCode
     */
    public function anInvalidIdentifierLocaleSpecificMessageShouldBeSent(string $pimAttributeCode): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(InvalidMappingException::class, $thrownException);
        Assert::assertEquals(
            InvalidMappingException::localeSpecificAttributeNotAllowed($pimAttributeCode)->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then a not existing identifier attribute message should be sent
     */
    public function aNotExistingIdentifierAttributeMessageShouldBeSent(): void
    {
        Assert::assertInstanceOf(\InvalidArgumentException::class, ExceptionContext::getThrownException());
    }

    /**
     * @Then a missing or invalid identifiers message should be sent
     */
    public function aMissingOrInvalidIdentifiersMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(InvalidMappingException::class, $thrownException);
        Assert::assertEquals(
            InvalidMappingException::missingOrInvalidIdentifiersInMapping([], 'foo')->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then a duplicate identifiers attribute message should be sent
     */
    public function aDuplicateIdentifiersAttributeMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(InvalidMappingException::class, $thrownException);
        Assert::assertEquals(
            InvalidMappingException::duplicateAttributeCode('', '')->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then an invalid brand mpn identifier message should be sent
     */
    public function anInvalidBrandMpnIdentifierMessageShouldBeSent(): void
    {
        $thrownException = ExceptionContext::getThrownException();
        Assert::assertInstanceOf(InvalidMappingException::class, $thrownException);
        Assert::assertEquals(
            InvalidMappingException::mandatoryAttributeMapping('foo', 'brand')->getMessage(),
            $thrownException->getMessage()
        );
    }

    /**
     * @Then /the products which need resubscribing should be identified for (.*)/
     *
     * @param string $franklinIdentifierCodes
     */
    public function theProductsWhichNeedResubscribingShouldBeIdentified(
        string $franklinIdentifierCodes
    ): void {
        $expectedCodes = explode(', ', str_replace(' and ', ', ', $franklinIdentifierCodes));
        Assert::assertNotEmpty($expectedCodes);
        foreach ($expectedCodes as $expectedCode) {
            Assert::assertContains($expectedCode, $this->identifyProductsToResubscribe->updatedIdentifierCodes());
        }
    }

    /**
     * @Then the products which need resubscribing should not be identified
     */
    public function theProductsWhichNeedResubscribingShouldNotBeIdentified(): void
    {
        Assert::assertEmpty(
            $this->identifyProductsToResubscribe->updatedIdentifierCodes()
        );
    }

    /**
     * Asserts that the identifiers mapping sent to Franklin is similar to the expected one.
     *
     * @param array $expectedMappings
     *
     * Expected Mapping format is:
     * [
     *     "asin" => "pim_asin",
     *     "upc"  => null
     * ]
     *
     * Identifiers mapping sent to Franklin is:
     * [
     *     [
     *         "from" => ["id" => "asin"]
     *         "status" => "active"
     *         "to" => ["id" => "pim_asin", "label" => ["en_US" => "My Asin"]]
     *     ],
     *     [
     *         "from" => ["id" => "upc"]
     *         "status" => "inactive"
     *         "to" => null
     *     ]
     * ]
     */
    private function assertIdentifiersMappingSentToFranklin(array $expectedMappings): void
    {
        $clientMappings = $this->fakeClient->getIdentifiersMapping();
        Assert::assertCount(count($expectedMappings), $clientMappings);

        $franklinMappings = new AttributesMapping($clientMappings);
        foreach ($franklinMappings as $index => $franklinMapping) {
            /** @var AttributeMapping $franklinMapping */
            $franklinCode = $franklinMapping->getTargetAttributeCode();
            $pimCode = $franklinMapping->getPimAttributeCode();
            $expectedPimCode = $expectedMappings[$franklinCode];

            Assert::assertArrayHasKey($franklinCode, $expectedMappings);
            Assert::assertEquals($expectedPimCode, $franklinMapping->getPimAttributeCode());
            $expectedStatus = (null === $pimCode) ? AttributeMapping::STATUS_INACTIVE : AttributeMapping::STATUS_ACTIVE;
            Assert::assertEquals($expectedStatus, $franklinMapping->getStatus());

            $this->assertLabelsSentToFranklin($pimCode, $clientMappings[$index]);
        }
    }

    /**
     * Asserts that identifiers labels sent to Franklin are the expected ones.
     *
     * @param string|null $pimCode
     * @param array $clientMapping
     */
    private function assertLabelsSentToFranklin(?string $pimCode, array $clientMapping): void
    {
        if (null !== $pimCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($pimCode);
            foreach ($attribute->getLabels() as $locale => $label) {
                Assert::assertEquals($label, $clientMapping['to']['label'][$locale]);
            }
        }
    }

    /**
     * Asserts that the persisted identifiers mapping is similar to the expected one.
     *
     * @param array $expectedMappings
     *
     * Expected Mapping format is:
     * [
     *     "asin" => "pim_asin",
     *     "upc"  => null
     * ]
     *
     * Identifiers mapping saved in Database is:
     * [
     *     "brand" => AttributeInterface::code "pim_asin",
     *     "upc"   => null
     * ]
     */
    private function assertIdentifiersMappingPersisted(array $expectedMappings): void
    {
        $persistedMappings = $this->identifiersMappingRepository->find();
        Assert::assertCount(count($expectedMappings), $persistedMappings);

        foreach ($expectedMappings as $expectedFranklinCode => $expectedPimCode) {
            $mappedAttributeCode = $persistedMappings->getMappedAttributeCode($expectedFranklinCode);
            if (null === $mappedAttributeCode) {
                Assert::assertNull($expectedPimCode);
            } else {
                Assert::assertEquals($expectedPimCode, (string) $mappedAttributeCode);
            }
        }
    }

    /**
     * Asserts the identifiers mapping is empty.
     */
    private function assertIdentifiersMappingIsEmpty(): void
    {
        $persistedIdentifiersMapping = $this->identifiersMappingRepository->find();
        Assert::assertTrue($persistedIdentifiersMapping->isEmpty());
        Assert::assertEquals([], $this->fakeClient->getIdentifiersMapping());
    }

    /**
     * Transforms from gherkin table:.
     *
     * | franklin_code | attribute_code |
     * | brand         | brand          |
     * | mpn           | mpn            |
     * | upc           | ean            |
     * | asin          | asin           |
     *
     * to php array with simple identifier mapping:
     *
     * franklin_code => attribute_code
     * [
     *     'brand' => 'brand',
     *     'mpn' => 'mpn',
     *     'upc' => 'ean',
     *     'asin' => 'asin',
     * ]
     *
     * @param TableNode $tableNode
     *
     * @return array
     */
    private function extractIdentifiersMappingFromTable(TableNode $tableNode): array
    {
        $identifiersMapping = array_fill_keys(IdentifiersMapping::FRANKLIN_IDENTIFIERS, null);

        foreach ($tableNode->getColumnsHash() as $column) {
            $franklinCode = $column['franklin_code'];
            if (!array_key_exists($franklinCode, $identifiersMapping)) {
                throw new \LogicException(
                    sprintf('Key "%s" is not part of the identifier mapping', $column['franklin_code'])
                );
            }
            $identifiersMapping[$franklinCode] = empty($column['attribute_code']) ? null : $column['attribute_code'];
        }

        return $identifiersMapping;
    }
}
