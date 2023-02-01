<?php

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

class UpdateFamilyNomenclatureContext implements Context
{
    private ?ViolationsException $violations = null;
    private const DEFAULT_OPERATOR = '<=';
    private const DEFAULT_VALUE = '3';

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
        $familyValues = [];
        foreach ($table as $line) {
            $familyValues[$line['familyCode']] = $line['value'];
        }
        $command = new UpdateNomenclatureCommand(
            propertyType: 'family',
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            values: $familyValues,
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When /^I (?:add|update) (.*) value for (.*)$/
     */
    public function iAddTheValueForFamily(string $value, string $familyCode)
    {
        $command = new UpdateNomenclatureCommand(
            propertyType: 'family',
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            values: [$familyCode => $value],
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I remove the :familyCode value
     */
    public function iRemoveTheFamilyValue(string $familyCode)
    {
        $command = new UpdateNomenclatureCommand(
            propertyType: 'family',
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            values: [$familyCode => null],
        );
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
     * @When /^I (create|update) the family nomenclature operator to (?P<operator>[^ ]*) and value to (?P<value>.*)$/
     */
    public function iUpdateTheFamilyNomenclatureOperatorTo(string $operator, string $value)
    {
        $command = new UpdateNomenclatureCommand(
            propertyType: 'family',
            operator: $operator,
            value: $value,
        );
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
