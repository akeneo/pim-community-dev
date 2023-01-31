<?php

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureValuesCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureValuesHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class UpdateFamilyNomenclatureContext implements Context
{
    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
        private readonly UpdateNomenclatureValuesHandler $updateNomenclatureValuesHandler,
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
        $command = new UpdateNomenclatureValuesCommand([$familyCode => $value]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I update :familyCode value to :value
     */
    public function iUpdateFamilyValueTo(string $familyCode, string $value)
    {
        $command = new UpdateNomenclatureValuesCommand([$familyCode => $value]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I remove the :familyCode value
     */
    public function iRemoveTheFamilyValue(string $familyCode)
    {
        $command = new UpdateNomenclatureValuesCommand([$familyCode => null]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @Then The value for :familyCode should be :value
     */
    public function theValueForFamilyShouldBe(string $familyCode, string $value)
    {
        Assert::assertEquals($this->nomenclatureValueRepository->get($familyCode), 'undefined' === $value ? null : $value);
    }
}
