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

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionMapping;
use Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Context\ExceptionContext;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsMappingContext implements Context
{
    /** @var GetAttributeOptionsMappingHandler */
    private $getAttributeOptionsMappingHandler;

    /** @var AttributeOptionsMapping */
    private $retrievedAttributeOptionsMapping;

    /** @var string */
    private $retrievedFamilyCode;

    /** @var string */
    private $retrievedFranklinAttributeId;

    /** @var SaveAttributeOptionsMappingHandler */
    private $saveAttributeOptionsMappingHandler;

    /** @var FakeClient */
    private $fakeClient;

    /** @var array */
    private $originalAttributeOptionsMapping;

    /**
     * @param GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler
     * @param SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler
     * @param FakeClient $fakeClient
     */
    public function __construct(
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler,
        SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler,
        FakeClient $fakeClient
    ) {
        $this->getAttributeOptionsMappingHandler = $getAttributeOptionsMappingHandler;
        $this->saveAttributeOptionsMappingHandler = $saveAttributeOptionsMappingHandler;
        $this->fakeClient = $fakeClient;
    }

    /**
     * @Given a predefined options mapping between Franklin attribute :franklinAttrId and PIM attribute :catalogAttrCode for family :familyCode as follows:
     *
     * @param string $franklinAttrId
     * @param string $catalogAttrCode
     * @param string $familyCode
     * @param TableNode $table
     */
    public function aPredefinedAttributeOptionsMapping(
        $franklinAttrId,
        $catalogAttrCode,
        $familyCode,
        TableNode $table
    ): void {
        $optionsMapping = $this->extractAttributeOptionsMappingFromTable($table);

        $command = new SaveAttributeOptionsMappingCommand(
            new FamilyCode($familyCode),
            new AttributeCode($catalogAttrCode),
            new FranklinAttributeId($franklinAttrId),
            new AttributeOptions($optionsMapping)
        );
        $this->saveAttributeOptionsMappingHandler->handle($command);

        $this->originalAttributeOptionsMapping = $this->fakeClient->getOptionsMapping();
    }

    /**
     * @When I retrieve the attribute options mapping for the family :familyCode and the attribute :franklinAttributeId
     *
     * @param mixed $familyCode
     * @param mixed $franklinAttributeId
     */
    public function iRetrieveTheAttributeOptionsMappingForTheFamilyAndTheAttribute(
        $familyCode,
        $franklinAttributeId
    ): void {
        $this->retrievedFamilyCode = $familyCode;
        $this->retrievedFranklinAttributeId = $franklinAttributeId;

        try {
            $query = new GetAttributeOptionsMappingQuery(
                new FamilyCode($familyCode),
                new FranklinAttributeId($franklinAttributeId)
            );
            $this->retrievedAttributeOptionsMapping = $this->getAttributeOptionsMappingHandler->handle($query);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When the Franklin :franklinAttrId options are mapped to the PIM :catalogAttrCode options for the family :familyCode as follows:
     *
     * @param string $franklinAttrId
     * @param string $catalogAttrCode
     * @param string $familyCode
     * @param TableNode $table
     */
    public function theAttributeOptionsAreMappedAsFollows(
        $franklinAttrId,
        $catalogAttrCode,
        $familyCode,
        TableNode $table
    ): void {
        $attributeOptionsMapping = $this->extractAttributeOptionsMappingFromTable($table);

        try {
            $command = new SaveAttributeOptionsMappingCommand(
                new FamilyCode($familyCode),
                new AttributeCode($catalogAttrCode),
                new FranklinAttributeId($franklinAttrId),
                new AttributeOptions($attributeOptionsMapping)
            );
            $this->saveAttributeOptionsMappingHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @When the Franklin :franklinAttrId options are mapped to the PIM :familyCode :catalogAttrCode options with an empty mapping
     *
     * @param string $franklinAttrId
     * @param string $catalogAttrCode
     * @param string $familyCode
     */
    public function theAttributeOptionsAreMappedWithAnEmptyMapping(
        $franklinAttrId,
        $catalogAttrCode,
        $familyCode
    ): void {
        try {
            $command = new SaveAttributeOptionsMappingCommand(
                new FamilyCode($familyCode),
                new AttributeCode($catalogAttrCode),
                new FranklinAttributeId($franklinAttrId),
                new AttributeOptions([])
            );
            $this->saveAttributeOptionsMappingHandler->handle($command);
        } catch (\Exception $e) {
            ExceptionContext::setThrownException($e);
        }
    }

    /**
     * @Then the retrieved attribute options mapping should be:
     */
    public function theRetrievedAttributeOptionsMappingShouldBe(TableNode $expectedMappingTable): void
    {
        Assert::eq($this->retrievedFamilyCode, $this->retrievedAttributeOptionsMapping->familyCode());
        Assert::eq($this->retrievedFranklinAttributeId, $this->retrievedAttributeOptionsMapping->franklinAttributeId());

        Assert::isInstanceOf($this->retrievedAttributeOptionsMapping, AttributeOptionsMapping::class);
        Assert::count(
            $this->retrievedAttributeOptionsMapping->mapping(),
            count($expectedMappingTable->getHash())
        );

        foreach ($this->retrievedAttributeOptionsMapping->mapping() as $index => $attributeOptionMapping) {
            $expectedRow = $expectedMappingTable->getHash()[$index];
            Assert::eq($attributeOptionMapping->franklinAttributeId(), $expectedRow['franklin_attribute_id']);
            Assert::eq($attributeOptionMapping->catalogAttributeCode(), $expectedRow['catalog_attribute_code']);
            $this->assertStatus($expectedRow['status'], $attributeOptionMapping->status());
        }
    }

    /**
     * @Then the retrieved attribute options should be empty
     */
    public function theRetrievedAttributeOptionsShouldBeEmpty()
    {
        Assert::count($this->retrievedAttributeOptionsMapping->mapping(), 0);
    }

    /**
     * @Then an invalid attribute message should be sent
     */
    public function anInvalidAttributeMessageShouldBeSent()
    {
        Assert::eq(ExceptionContext::getThrownException(), \InvalidArgumentException::class);
    }

    /**
     * @Then the attribute options mapping should be:
     *
     * @param TableNode $optionsMapping
     */
    public function theAttributeOptionsMappingShouldBe(TableNode $optionsMapping): void
    {
        $expectedOptionsMapping = [];
        foreach ($optionsMapping->getHash() as $option) {
            $to = null;
            if (!empty($option['catalog_attribute_option_code'])) {
                $to = [
                    'id' => $option['catalog_attribute_option_code'],
                    'label' => null,
                ];
            }

            $expectedOptionsMapping[] = [
                'from' => [
                    'id' => $option['franklin_attribute_option_id'],
                    'label' => [
                        'en_US' => $option['franklin_attribute_option_label'],
                    ],
                ],
                'to' => $to,
                'status' => null === $to ? OptionMapping::STATUS_INACTIVE : OptionMapping::STATUS_ACTIVE,
            ];
        }

        Assert::eq($expectedOptionsMapping, $this->fakeClient->getOptionsMapping());
    }

    /**
     * @Then the attribute options mapping should not be saved
     */
    public function theAttributeOptionsMappingShouldNotBeSaved(): void
    {
        $clientAttributeOptionsMapping = $this->fakeClient->getOptionsMapping();

        Assert::isEmpty($clientAttributeOptionsMapping);
        Assert::isInstanceOf(ExceptionContext::getThrownException(), \Exception::class);
    }

    /**
     * Asserts status.
     *
     * @param string $expectedStatus
     * @param int $status
     *
     * @throws \Exception
     */
    private function assertStatus(string $expectedStatus, int $status): void
    {
        switch ($expectedStatus) {
            case 'pending':
                Assert::eq(AttributeOptionMapping::STATUS_PENDING, $status);
                break;
            case 'active':
                Assert::eq(AttributeOptionMapping::STATUS_ACTIVE, $status);
                break;
            case 'inactive':
                Assert::eq(AttributeOptionMapping::STATUS_INACTIVE, $status);
                break;
            default:
                throw new \Exception(sprintf('Status "%s" does not match any expected value', $expectedStatus));
        }
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function extractAttributeOptionsMappingFromTable(TableNode $table): array
    {
        $attributeOptions = [];
        foreach ($table->getHash() as $option) {
            $attributeOptions[$option['franklin_attribute_option_id']] = [
                'franklinAttributeOptionCode' => [
                    'label' => $option['franklin_attribute_option_label'],
                ],
                'catalogAttributeOptionCode' => $option['catalog_attribute_option_code'],
                'status' => 'active' == $option['status'] ? 1 : 0,
            ];
        }

        return $attributeOptions;
    }
}
