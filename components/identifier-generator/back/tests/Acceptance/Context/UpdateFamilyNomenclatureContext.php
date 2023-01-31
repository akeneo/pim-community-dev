<?php

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class UpdateFamilyNomenclatureContext implements Context
{
    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
    ) {
    }

    /**
     * @Given a family nomenclature with the following values
     */
    public function aFamilyNomenclatureWithTheFollowingValues(TableNode $table)
    {
        foreach ($table as $line) {
            $this->nomenclatureValueRepository->set($line['familyCode'], $line['value']);
        }
    }

    /**
     * @When I add the value :value for :familyCode
     */
    public function iAddTheValueForFamily(string $value, string $familyCode)
    {
        $this->nomenclatureValueRepository->set($familyCode, $value);
    }

    /**
     * @Then The value for :familyCode should be :value
     */
    public function theValueForFamilyShouldBe(string $familyCode, string $value)
    {
        Assert::assertEquals($this->nomenclatureValueRepository->get($familyCode), 'undefined' === $value ? null : $value);
    }

    /**
     * @When I update :familyCode value to :value
     */
    public function iUpdateFamilyValueTo(string $familyCode, string $value)
    {
        $this->nomenclatureValueRepository->set($familyCode, $value);
    }

    /**
     * @When I remove the :familyCode value
     */
    public function iRemoveTheFamilyValue(string $familyCode)
    {
        $this->nomenclatureValueRepository->set($familyCode, null);
    }
}
