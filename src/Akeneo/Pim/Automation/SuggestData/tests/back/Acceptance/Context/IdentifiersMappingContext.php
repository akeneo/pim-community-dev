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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
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

    /**
     * @param ManageIdentifiersMapping              $manageIdentifiersMapping
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param AttributeRepositoryInterface          $attributeRepository
     */
    public function __construct(
        ManageIdentifiersMapping $manageIdentifiersMapping,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->manageIdentifiersMapping = $manageIdentifiersMapping;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->attributeRepository = $attributeRepository;
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
     */
    public function aPredefinedMapping(TableNode $table): void
    {
        $mapped = $this->getTableNodeAsArrayWithoutHeaders($table);
        $identifiers = IdentifiersMapping::PIM_AI_IDENTIFIERS;

        $tmp = array_fill_keys($identifiers, null);
        $tmp = array_merge($tmp, $mapped);

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
                $this->getTableNodeAsArrayWithoutHeaders($table)
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
     * @Then the identifiers mapping should be defined as follows:
     *
     * @param TableNode $table
     */
    public function theIdentifiersMappingIsDefined(TableNode $table): void
    {
        $databaseIdentifiers = $this->identifiersMappingRepository->find()->getIdentifiers();

        $identifiers = $this->getTableNodeAsArrayWithoutHeaders($table);

        foreach ($identifiers as $pimAiCode => $attributeCode) {
            $identifiers[$pimAiCode] = $this->attributeRepository->findOneByIdentifier($attributeCode);
        }

        Assert::assertEquals($identifiers, $databaseIdentifiers);
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
        $identifiers = $this->getTableNodeAsArrayWithoutHeaders($table);

        Assert::assertEquals($identifiers, $this->manageIdentifiersMapping->getIdentifiersMapping());
    }

    /**
     * @Then the identifiers mapping should be valid
     */
    public function theIdentifiersMappingShouldBeValid(): void
    {
        $identifiers = $this->identifiersMappingRepository->find();
        Assert::assertFalse($identifiers->isEmpty());
    }

    private function assertMappingIsEmpty(): void
    {
        $identifiers = $this->identifiersMappingRepository->find()->getIdentifiers();

        Assert::assertEquals([], $identifiers);
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
}
