<?php

declare(strict_types=1);

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
    private const DEFAULT_GENERATE_IF_EMPTY = false;

    public function __construct(
        private readonly NomenclatureValueRepository $nomenclatureValueRepository,
        private readonly NomenclatureDefinitionRepository $nomenclatureDefinitionRepository,
        private readonly UpdateNomenclatureHandler $updateNomenclatureValuesHandler,
    ) {
    }

    /**
     * @Given a family nomenclature with the following values
     */
    public function aFamilyNomenclatureWithTheFollowingValues(TableNode $table): void
    {
        $familyValues = [];
        foreach ($table as $line) {
            $familyValues[$line['familyCode']] = $line['value'];
        }
        $command = new UpdateNomenclatureCommand(
            propertyCode: 'family',
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            generateIfEmpty: self::DEFAULT_GENERATE_IF_EMPTY,
            values: $familyValues,
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When /^I (?:add|update) (.*) value for (.*)$/
     */
    public function iAddTheValueForFamily(string $value, string $familyCode): void
    {
        $command = new UpdateNomenclatureCommand(
            propertyCode: 'family',
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            generateIfEmpty: self::DEFAULT_GENERATE_IF_EMPTY,
            values: [$familyCode => $value],
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I remove the :familyCode value
     */
    public function iRemoveTheFamilyValue(string $familyCode): void
    {
        $command = new UpdateNomenclatureCommand(
            propertyCode: 'family',
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            generateIfEmpty: self::DEFAULT_GENERATE_IF_EMPTY,
            values: [$familyCode => null],
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @Then The value for :familyCode should be :value
     */
    public function theValueForFamilyShouldBe(string $familyCode, string $value): void
    {
        Assert::eq($this->nomenclatureValueRepository->get($familyCode), 'undefined' === $value ? null : $value);
    }

    /**
     * @When /^I (create|update) the family nomenclature operator to (?P<operator>[^ ]*), value to (?P<value>.*) and(?P<no> no)? generation if empty$/
     */
    public function iUpdateTheFamilyNomenclatureOperatorTo(string $operator, string $value, string $no = ''): void
    {
        $command = new UpdateNomenclatureCommand(
            propertyCode: 'family',
            operator: $operator,
            value: \intval($value),
            generateIfEmpty: $no !== '',
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
    public function theFamilyNomenclatureOperatorShouldBe(string $operator): void
    {
        Assert::eq($this->nomenclatureDefinitionRepository->get('family')->operator(), $operator);
    }

    /**
     * @Then the family nomenclature value should be :value
     */
    public function theFamilyNomenclatureValueShouldBe(string $value): void
    {
        Assert::eq($this->nomenclatureDefinitionRepository->get('family')->value(), \intval($value));
    }

    /**
     * @Then the family nomenclature generation if empty should be :generateIfEmpty
     */
    public function theFamilyNomenclatureGenerationIfEmptyShouldBe(string $generateIfEmpty): void
    {
        Assert::eq($this->nomenclatureDefinitionRepository->get('family')->generateIfEmpty(), $generateIfEmpty === 'true');
    }

    /**
     * @Then I should have an error :message
     */
    public function iShouldHaveAnError($message): void
    {
        Assert::notNull($this->violations, 'No error were raised.');
        Assert::contains($this->violations->getMessage(), $message);
    }

    /**
     * @Then I should not have an error
     */
    public function iShouldNotHaveAnError(): void
    {
        Assert::null($this->violations);
    }
}
