<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\SuggestData\Context;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PimEnterprise\Component\SuggestData\Application\ManageMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;
use PHPUnit\Framework\Assert;

class IdentifiersMapping implements Context
{
    private
        $manageMapping,
        $identifiersMappingRepository,
        $attributeRepository;

    public function __construct(ManageMapping $manageMapping, IdentifiersMappingRepositoryInterface $identifiersMappingRepository, AttributeRepositoryInterface $attributeRepository)
    {
        $this->manageMapping = $manageMapping;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @When the identifiers are mapped with valid values as follows:
     *
     * @param TableNode $table
     */
    public function theIdentifiersAreMappedWithValidValues(TableNode $table)
    {
        try {
            $this->manageMapping->updateIdentifierMapping(
                $this->getTableNodeAsArrayWithoutHeaders($table)
            );

            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @Then the identifier mapping is defined as follows:
     *
     * @param TableNode $table
     */
    public function theIdentifiersMappingIsDefined(TableNode $table)
    {
        $databaseIdentifiers = $this->identifiersMappingRepository->findAll()->getIdentifiers();

        $identifiers = $this->getTableNodeAsArrayWithoutHeaders($table);

        foreach ($identifiers as $pimAiCode => $attributeCode) {
            $identifiers[$pimAiCode] = $this->attributeRepository->findOneByIdentifier($attributeCode);
        }

        Assert::assertEquals($identifiers, $databaseIdentifiers);
    }

    /**
     * @When the identifiers are mapped with invalid values as follows:
     *
     * @param TableNode $table
     */
    public function theIdentifiersAreMappedWithInvalidValues(TableNode $table)
    {
        try {
            $this->manageMapping->updateIdentifierMapping(
                $this->getTableNodeAsArrayWithoutHeaders($table)
            );

            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @Then the identifiers mapping is not defined
     */
    public function theIdentifiersMappingIsNotDefined()
    {
        $identifiers = $this->identifiersMappingRepository->findAll()->getIdentifiers();

        Assert::assertEquals([], $identifiers);
    }

    /**
     * @Given a predefined mapping as follows:
     *
     * @param TableNode $table
     */
    public function aPredefinedMapping(TableNode $table) {
        $this->manageMapping->updateIdentifierMapping(
            $this->getTableNodeAsArrayWithoutHeaders($table)
        );
    }

    /**
     * @Then the retrieved mapping is the following:
     *
     * @param TableNode $table
     */
    public function theRetrievedMappingIsTheFollowing(TableNode $table)
    {
        $identifiers = $this->getTableNodeAsArrayWithoutHeaders($table);

        Assert::assertEquals($identifiers, $this->manageMapping->getIdentifiersMapping());
    }

    /**
     * @param TableNode $tableNode
     *
     * @return array
     */
    private function getTableNodeAsArrayWithoutHeaders(TableNode $tableNode) {
        $return = $tableNode->getRowsHash();
        array_shift($return);

        return $return;
    }
}
