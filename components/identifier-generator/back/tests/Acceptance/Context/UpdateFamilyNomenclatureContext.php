<?php

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureValuesCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureValuesHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

class UpdateFamilyNomenclatureContext implements Context
{
    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
        private readonly NomenclatureDefinitionRepository $nomenclatureDefinitionRepository,
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
        $command = new UpdateNomenclatureValuesCommand(values:[$familyCode => $value]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I update :familyCode value to :value
     */
    public function iUpdateFamilyValueTo(string $familyCode, string $value)
    {
        $command = new UpdateNomenclatureValuesCommand(values:[$familyCode => $value]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I remove the :familyCode value
     */
    public function iRemoveTheFamilyValue(string $familyCode)
    {
        $command = new UpdateNomenclatureValuesCommand(values:[$familyCode => null]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @Then The value for :familyCode should be :value
     */
    public function theValueForFamilyShouldBe(string $familyCode, string $value)
    {
        Assert::assertEquals($this->nomenclatureValueRepository->get($familyCode), 'undefined' === $value ? null : $value);
    }

    /**
     * @When /^I update the family nomenclature operator to (?P<operator>.*)$/
     */
    public function iUpdateTheFamilyNomenclatureOperatorTo(string $operator)
    {
        $command = new UpdateNomenclatureValuesCommand(operator: $operator);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @Then /^the family nomenclature operator should be (?P<operator>.*)$/
     */
    public function theFamilyNomenclatureOperatorShouldBe(string $operator)
    {
        Assert::assertEquals($this->nomenclatureDefinitionRepository->get('family')->operator(), $operator);
    }

    /**
     * @Given a family nomenclature definition
     */
    public function aFamilyNomenclatureDefinition()
    {
        $this->nomenclatureDefinitionRepository->create('family', new NomenclatureDefinition('=', 5));
    }

    /**
     * @When I update the family nomenclature value to :value
     */
    public function iUpdateTheFamilyNomenclatureValueTo(string $value)
    {
        $command = new UpdateNomenclatureValuesCommand(value: \intval($value));
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @Then the family nomenclature value should be :value
     */
    public function theFamilyNomenclatureValueShouldBe(string $value)
    {
        Assert::assertEquals($this->nomenclatureDefinitionRepository->get('family')->value(), \intval($value));
    }
}
