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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionMapping;
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
    private $attributeOptionsMapping;

    /** @var string */
    private $familyCode;

    /** @var string */
    private $franklinAttributeId;

    /** @var SaveAttributeOptionsMappingHandler */
    private $saveAttributeOptionsMappingHandler;
    /**
     * @var FakeClient
     */
    private $fakeClient;

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
     * @When I retrieve the attribute options mapping for the family :familyCode and the attribute :franklinAttributeId
     *
     * @param mixed $familyCode
     * @param mixed $franklinAttributeId
     */
    public function iRetrieveTheAttributeOptionsMappingForTheFamilyAndTheAttribute(
        $familyCode,
        $franklinAttributeId
    ): void {
        $this->familyCode = $familyCode;
        $this->franklinAttributeId = $franklinAttributeId;

        $query = new GetAttributeOptionsMappingQuery(
            new FamilyCode($familyCode),
            new FranklinAttributeId($franklinAttributeId)
        );
        $this->attributeOptionsMapping = $this->getAttributeOptionsMappingHandler->handle($query);
        Assert::isInstanceOf($this->attributeOptionsMapping, AttributeOptionsMapping::class);
    }

    /**
     * @When the Franklin :attrId options are mapped to the PIM :attrCode options for the family :family as follows:
     *
     * @param $attrId
     * @param $attrCode
     * @param $family
     * @param TableNode $optionsMapping
     */
    public function theAttributeOptionsAreMappedAsFollows(
        $attrId,
        $attrCode,
        $family,
        TableNode $optionsMapping
    ): void {
        $attributeOptions = [];
        foreach ($optionsMapping->getHash() as $option) {
            $attributeOptions[$option['franklin_attribute_option_id']] = [
                'franklinAttributeOptionCode' => [
                    'label' => $option['franklin_attribute_option_label'],
                ],
                'catalogAttributeOptionCode' => $option['catalog_attribute_option_code'],
                'status' => 'active' == $option['status'] ? 1 : 0,
            ];
        }

        $command = new SaveAttributeOptionsMappingCommand(
            new FamilyCode($family),
            new AttributeCode($attrCode),
            new FranklinAttributeId($attrId),
            new AttributeOptions($attributeOptions)
        );
        $this->saveAttributeOptionsMappingHandler->handle($command);
    }

    /**
     * @Then the retrieved attribute options mapping should be:
     */
    public function theRetrievedAttributeOptionsMappingShouldBe(TableNode $expectedMappingTable): void
    {
        Assert::eq($this->familyCode, $this->attributeOptionsMapping->familyCode());
        Assert::eq($this->franklinAttributeId, $this->attributeOptionsMapping->franklinAttributeId());

        Assert::count(
            $this->attributeOptionsMapping->mapping(),
            count($expectedMappingTable->getHash())
        );

        foreach ($this->attributeOptionsMapping->mapping() as $index => $attributeOptionMapping) {
            $expectedRow = $expectedMappingTable->getHash()[$index];
            Assert::eq($attributeOptionMapping->franklinAttributeId(), $expectedRow['franklin_attribute_id']);
            Assert::eq($attributeOptionMapping->catalogAttributeCode(), $expectedRow['catalog_attribute_code']);
            $this->assertStatus($expectedRow['status'], $attributeOptionMapping->status());
        }
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
     * Asserts status.
     *
     * @param string $expectedStatus
     * @param int $status
     */
    private function assertStatus($expectedStatus, $status): void
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
                throw \Exception(sprintf('Status "%s" does not match any expected value', $expectedStatus));
        }
    }
}
