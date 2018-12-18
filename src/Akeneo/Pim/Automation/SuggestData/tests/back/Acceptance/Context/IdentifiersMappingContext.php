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

namespace Akeneo\Test\Pim\Automation\SuggestData\Acceptance\Context;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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

    /** @var UpdateIdentifiersMappingHandler */
    private $updateIdentifiersMappingHandler;

    /** @var FakeClient */
    private $fakeClient;

    /** @var \Exception */
    private $thrownException;

    /**
     * @param GetIdentifiersMappingHandler $getIdentifiersMappingHandler
     * @param UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FakeClient $fakeClient
     */
    public function __construct(
        GetIdentifiersMappingHandler $getIdentifiersMappingHandler,
        UpdateIdentifiersMappingHandler $updateIdentifiersMappingHandler,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        FakeClient $fakeClient
    ) {
        $this->getIdentifiersMappingHandler = $getIdentifiersMappingHandler;
        $this->updateIdentifiersMappingHandler = $updateIdentifiersMappingHandler;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->attributeRepository = $attributeRepository;
        $this->fakeClient = $fakeClient;
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
     * @throws InvalidMappingException
     */
    public function aPredefinedIdentifiersMapping(TableNode $table): void
    {
        $identifiersMapping = $this->extractIdentifiersMappingFromTable($table);

        $command = new UpdateIdentifiersMappingCommand($identifiersMapping);
        $this->updateIdentifiersMappingHandler->handle($command);
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
            $command = new UpdateIdentifiersMappingCommand($identifiersMapping);
            $this->updateIdentifiersMappingHandler->handle($command);
        } catch (InvalidMappingException | DataProviderException $exception) {
            $this->thrownException = $exception;
        }
    }

    /**
     * @Then an invalid mapping message should be sent
     */
    public function anInvalidMappingMessageShouldBeSent(): void
    {
        Assert::assertInstanceOf(\Exception::class, $this->thrownException);
        Assert::assertNotEmpty($this->thrownException->getMessage());
    }

    /**
     * @When the identifiers are mapped with empty values
     */
    public function theIdentifiersMappingIsSavedWithEmptyValues(): void
    {
        try {
            $command = new UpdateIdentifiersMappingCommand([]);
            $this->updateIdentifiersMappingHandler->handle($command);
        } catch (InvalidMappingException $e) {
            $this->thrownException = $e;
        }
    }

    /**
     * @Then the identifiers mapping should not be saved
     */
    public function theIdentifiersMappingShouldNotBeSaved(): void
    {
        $this->assertIdentifiersMappingIsEmpty();
    }

    /**
     * @Then the retrieved identifiers mapping should be the following:
     *
     * @param TableNode $table
     */
    public function theRetrievedIdentifiersMappingIsTheFollowing(TableNode $table): void
    {
        $expectedIdentifiersMapping = $this->extractIdentifiersMappingFromTable($table);

        $this->assertIdentifiersMappingSentToFranklin($expectedIdentifiersMapping);
        $this->assertIdentifiersMappingPersisted($expectedIdentifiersMapping);
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
            foreach ($attribute->getTranslations() as $translation) {
                $locale = $translation->getLocale();
                $label = $translation->getLabel();
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
            $persistedMapping = $persistedMappings->getIdentifier($expectedFranklinCode);
            if (null === $persistedMapping) {
                Assert::assertNull($expectedPimCode);
            } else {
                Assert::assertEquals($expectedPimCode, $persistedMapping->getCode());
            }
        }
    }

    /**
     * Asserts the identifiers mapping is empty.
     */
    private function assertIdentifiersMappingIsEmpty(): void
    {
        $persistedIdentifiers = $this->identifiersMappingRepository->find()->getIdentifiers();

        Assert::assertEquals([], $persistedIdentifiers);
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
