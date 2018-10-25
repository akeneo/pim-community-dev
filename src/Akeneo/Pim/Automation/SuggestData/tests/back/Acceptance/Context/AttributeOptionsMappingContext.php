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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
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

    /**
     * @param GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler
     */
    public function __construct(
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler
    ) {
        $this->getAttributeOptionsMappingHandler = $getAttributeOptionsMappingHandler;
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
