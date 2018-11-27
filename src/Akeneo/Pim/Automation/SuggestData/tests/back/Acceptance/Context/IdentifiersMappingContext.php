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
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetIdentifiersMappingQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\InternalApi\Normalizer\IdentifiersMappingNormalizer;
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
        $this->assertMappingIsEmpty();
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
        $mapped = $this->extractIdentifiersMappingFromTable($table);
        $identifiers = IdentifiersMapping::FRANKLIN_IDENTIFIERS;

        $tmp = array_fill_keys($identifiers, null);
        $tmp = array_merge($tmp, $mapped);
        $tmp = array_map(function ($value) {
            return '' !== $value ? $value : null;
        }, $tmp);

        $command = new UpdateIdentifiersMappingCommand($tmp);
        $this->updateIdentifiersMappingHandler->handle($command);
    }

    /**
     * @When the identifiers are mapped with valid values as follows:
     *
     * @param TableNode $table
     *
     * @return bool
     */
    public function theIdentifiersAreMappedWithValidValues(TableNode $table): bool
    {
        try {
            $command = new UpdateIdentifiersMappingCommand(
                $this->extractIdentifiersMappingFromTable($table)
            );
            $this->updateIdentifiersMappingHandler->handle($command);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @When the identifiers are mapped with invalid values as follows:
     *
     * @param TableNode $table
     *
     * @return bool
     */
    public function theIdentifiersAreMappedWithInvalidValues(TableNode $table): bool
    {
        try {
            $command = new UpdateIdentifiersMappingCommand(
                $this->getTableNodeAsArrayWithoutHeaders($table)
            );
            $this->updateIdentifiersMappingHandler->handle($command);

            return false;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * @When the identifiers mapping is saved with empty values
     *
     * @return bool
     */
    public function theIdentifiersMappingIsSavedWithEmptyValues(): bool
    {
        try {
            $command = new UpdateIdentifiersMappingCommand([]);
            $this->updateIdentifiersMappingHandler->handle($command);

            return false;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * @Then the identifiers mapping should not be defined
     */
    public function theIdentifiersMappingIsNotDefined(): void
    {
        $this->assertMappingIsEmpty();
    }

    /**
     * @Then the identifiers mapping should not be saved
     */
    public function theIdentifiersMappingIsNotSaved(): void
    {
        $this->assertMappingIsEmpty();
    }

    /**
     * @Then the retrieved mapping should be the following:
     *
     * @param TableNode $table
     */
    public function theRetrievedMappingIsTheFollowing(TableNode $table): void
    {
        $identifiers = $this->extractIdentifiersMappingFromTable($table);

        $identifiersMappingNormalizer = new IdentifiersMappingNormalizer();
        $identifiersMapping = $this->getIdentifiersMappingHandler->handle(new GetIdentifiersMappingQuery());
        $normalizedIdentifiers = $identifiersMappingNormalizer->normalize($identifiersMapping->getIdentifiers());

        Assert::assertEquals($identifiers, $normalizedIdentifiers);
        Assert::assertEquals(
            $this->extractIdentifiersMappingToFranklinFormatFromTable($table),
            $this->fakeClient->getIdentifiersMapping()
        );
    }

    private function assertMappingIsEmpty(): void
    {
        $identifiers = $this->identifiersMappingRepository->find()->getIdentifiers();

        Assert::assertEquals([], $identifiers);
        Assert::assertEquals([], $this->fakeClient->getIdentifiersMapping());
    }

    /**
     * @param TableNode $tableNode
     *
     * @return array
     */
    private function getTableNodeAsArrayWithoutHeaders(TableNode $tableNode): array
    {
        $extractedData = $tableNode->getRowsHash();
        array_shift($extractedData);

        $identifiersMapping = array_fill_keys(IdentifiersMapping::FRANKLIN_IDENTIFIERS, null);

        return array_merge($identifiersMapping, $extractedData);
    }

    /**
     * Transforms from gherkin table:.
     *
     *                                | Not mandatory  | as much locales as you want ...
     * | franklin_code | attribute_code | en_US | fr_FR  |
     * | brand       | brand          | Brand | Marque |
     * | mpn         | mpn            | MPN   | MPN    |
     * | upc         | ean            | EAN   | EAN    |
     * | asin        | asin           | ASIN  | ASIN   |
     *
     * to php array Franklin format:
     *
     * [
     *     [
     *         'from' => ['id' => 'brand'], (franklin_code)
     *         'status' => 'active',
     *         'to' => [
     *             'id' => 'brand', (attribute_code)
     *             'label' => [
     *                 'en_US' => 'Brand',
     *                 'fr_FR' => 'Marque',
     *                 etc.
     *             ],
     *         ]
     *     ], etc.
     * ]
     *
     * @param TableNode $tableNode
     *
     * @return array
     */
    private function extractIdentifiersMappingToFranklinFormatFromTable(TableNode $tableNode): array
    {
        $extractedData = $tableNode->getRows();
        $indexes = array_shift($extractedData);
        $locales = array_filter($indexes, function ($value) {
            return 'franklin_code' !== $value && 'attribute_code' !== $value;
        });

        $mappings = [];
        foreach ($extractedData as $data) {
            $rawMapping = array_combine($indexes, $data);
            $labels = [];
            foreach ($locales as $locale) {
                if ('' !== $rawMapping[$locale]) {
                    $labels[$locale] = $rawMapping[$locale];
                }
            }

            $mappings[$rawMapping['franklin_code']] = [
                'from' => ['id' => $rawMapping['franklin_code']],
                'status' => empty($rawMapping['attribute_code']) ? 'inactive' : 'active',
            ];

            if (!empty($rawMapping['attribute_code'])) {
                $mappings[$rawMapping['franklin_code']]['to'] = [
                    'id' => $rawMapping['attribute_code'],
                    'label' => $labels,
                ];
            }
        }

        return array_values($mappings);
    }

    /**
     * Transforms from gherkin table:.
     *
     *                                | Not mandatory and will not be part of extraction |
     * | franklin_code | attribute_code | en_US | fr_FR  |
     * | brand       | brand          | Brand | Marque |
     * | mpn         | mpn            | MPN   | MPN    |
     * | upc         | ean            | EAN   | EAN    |
     * | asin        | asin           | ASIN  | ASIN   |
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
        $mapping = [];
        foreach ($tableNode->getColumnsHash() as $column) {
            $mapping[$column['franklin_code']] = $column['attribute_code'];
        }

        $identifiersMapping = array_fill_keys(IdentifiersMapping::FRANKLIN_IDENTIFIERS, null);

        return array_merge($identifiersMapping, $mapping);
    }
}
