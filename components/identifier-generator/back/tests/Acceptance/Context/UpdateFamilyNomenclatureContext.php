<?php

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

class UpdateFamilyNomenclatureContext implements Context
{
    private ?ViolationsException $violations = null;

    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
        private readonly NomenclatureDefinitionRepository $nomenclatureDefinitionRepository,
        private readonly UpdateNomenclatureHandler $updateNomenclatureValuesHandler,
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
        $command = new UpdateNomenclatureCommand(values:[$familyCode => $value]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I update :familyCode value to :value
     */
    public function iUpdateFamilyValueTo(string $familyCode, string $value)
    {
        $command = new UpdateNomenclatureCommand(values:[$familyCode => $value]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I remove the :familyCode value
     */
    public function iRemoveTheFamilyValue(string $familyCode)
    {
        $command = new UpdateNomenclatureCommand(values:[$familyCode => null]);
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @Then The value for :familyCode should be :value
     */
    public function theValueForFamilyShouldBe(string $familyCode, string $value)
    {
        Assert::eq($this->nomenclatureValueRepository->get($familyCode), 'undefined' === $value ? null : $value);
    }

    /**
     * @When /^I update the family nomenclature operator to (?P<operator>.*)$/
     */
    public function iUpdateTheFamilyNomenclatureOperatorTo(string $operator)
    {

        $command = new UpdateNomenclatureCommand(operator: $operator);
        try {
            ($this->updateNomenclatureValuesHandler)($command);
        } catch (ViolationsException $e) {
            $this->violations = $e;
        }
    }

    /**
     * @Then /^the family nomenclature operator should be (?P<operator>.*)$/
     */
    public function theFamilyNomenclatureOperatorShouldBe(string $operator)
    {
        Assert::eq($this->nomenclatureDefinitionRepository->get('family')->operator(), $operator);
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
        $command = new UpdateNomenclatureCommand(value: \intval($value));
        try {
            ($this->updateNomenclatureValuesHandler)($command);
        } catch (ViolationsException $e) {
            $this->violations = $e;
        }
    }

    /**
     * @Then the family nomenclature value should be :value
     */
    public function theFamilyNomenclatureValueShouldBe(string $value)
    {
        Assert::eq($this->nomenclatureDefinitionRepository->get('family')->value(), \intval($value));
    }

    /**
     * @Then I should have an error :message
     */
    public function iShouldHaveAnError($message)
    {
        Assert::notNull($this->violations, 'No error were raised.');
        Assert::contains($this->violations->getMessage(), $message);
    }
}
