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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Service\ManageIdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingApiFake;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class IdentifiersMappingContext implements Context
{
    /** @var ManageIdentifiersMapping */
    private $manageIdentifiersMapping;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiersMappingApiFake */
    private $identifiersMappingApiFake;

    /**
     * @param ManageIdentifiersMapping $manageIdentifiersMapping
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param IdentifiersMappingApiFake $identifiersMappingApiFake
     */
    public function __construct(
        ManageIdentifiersMapping $manageIdentifiersMapping,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingApiFake $identifiersMappingApiFake
    ) {
        $this->manageIdentifiersMapping = $manageIdentifiersMapping;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->attributeRepository = $attributeRepository;
        $this->identifiersMappingApiFake = $identifiersMappingApiFake;
    }

    /**
     * @Given an empty identifiers mapping
     */
    public function anEmptyIdentifiersMapping(): void
    {
        $this->assertMappingIsEmpty();
    }

    /**
     * @Given a predefined mapping as follows:
     *
     * @param TableNode $table
     *
     * @throws InvalidMappingException
     */
    public function aPredefinedMapping(TableNode $table): void
    {
        $mapped = $this->extractIdentifiersMappingFromTable($table);
        $identifiers = IdentifiersMapping::PIM_AI_IDENTIFIERS;

        $tmp = array_fill_keys($identifiers, null);
        $tmp = array_merge($tmp, $mapped);
        $tmp = array_map(function ($value) {
            return '' !== $value ? $value : null;
        }, $tmp);

        $this->manageIdentifiersMapping->updateIdentifierMapping($tmp);
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
            $this->manageIdentifiersMapping->updateIdentifierMapping(
                $this->extractIdentifiersMappingFromTable($table)
            );

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
            $this->manageIdentifiersMapping->updateIdentifierMapping(
                $this->getTableNodeAsArrayWithoutHeaders($table)
            );

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
            $this->manageIdentifiersMapping->updateIdentifierMapping([]);

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

        Assert::assertEquals($identifiers, $this->manageIdentifiersMapping->getIdentifiersMapping());
        Assert::assertEquals(
            $this->extractIdentifiersMappingToFranklinFormatFromTable($table),
            $this->identifiersMappingApiFake->get()
        );
    }

    private function assertMappingIsEmpty(): void
    {
        $identifiers = $this->identifiersMappingRepository->find()->getIdentifiers();

        Assert::assertEquals([], $identifiers);
        Assert::assertEquals([], $this->identifiersMappingApiFake->get());
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

        $identifiersMapping = array_fill_keys(IdentifiersMapping::PIM_AI_IDENTIFIERS, null);

        return array_merge($identifiersMapping, $extractedData);
    }

    /**
     * Transforms from gherkin table:.
     *
     *                                | Not mandatory  | as much locales as you want ...
     * | pim_ai_code | attribute_code | en_US | fr_FR  |
     * | brand       | brand          | Brand | Marque |
     * | mpn         | mpn            | MPN   | MPN    |
     * | upc         | ean            | EAN   | EAN    |
     * | asin        | asin           | ASIN  | ASIN   |
     *
     * to php array Franklin format:
     *
     * [
     *     [
     *         'from' => ['id' => 'brand'], (pim_ai_code)
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
            return 'pim_ai_code' !== $value && 'attribute_code' !== $value;
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

            $mappings[$rawMapping['pim_ai_code']] = [
                'from' => ['id' => $rawMapping['pim_ai_code']],
                'status' => empty($rawMapping['attribute_code']) ? 'inactive' : 'active',
            ];

            if (!empty($rawMapping['attribute_code'])) {
                $mappings[$rawMapping['pim_ai_code']]['to'] = [
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
     * | pim_ai_code | attribute_code | en_US | fr_FR  |
     * | brand       | brand          | Brand | Marque |
     * | mpn         | mpn            | MPN   | MPN    |
     * | upc         | ean            | EAN   | EAN    |
     * | asin        | asin           | ASIN  | ASIN   |
     *
     * to php array with simple identifier mapping:
     *
     * pim_ai_code => attribute_code
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
            $mapping[$column['pim_ai_code']] = $column['attribute_code'];
        }

        $identifiersMapping = array_fill_keys(IdentifiersMapping::PIM_AI_IDENTIFIERS, null);

        return array_merge($identifiersMapping, $mapping);
    }
}
