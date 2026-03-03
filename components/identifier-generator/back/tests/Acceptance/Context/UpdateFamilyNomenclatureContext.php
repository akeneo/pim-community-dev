<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

final class UpdateFamilyNomenclatureContext implements Context
{
    private const DEFAULT_OPERATOR = '<=';
    private const DEFAULT_VALUE = 3;
    private const DEFAULT_GENERATE_IF_EMPTY = false;

    public function __construct(
        private readonly ViolationsContext $violationsContext,
        private readonly FamilyNomenclatureRepository $nomenclatureRepository,
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
     * @When /^I (?:add|update) (.*) value for (.*) family$/
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
     * @Then The value for :familyCode should be :expectedValue
     */
    public function theValueForFamilyShouldBe(string $familyCode, string $expectedValue): void
    {
        $nomenclature = $this->nomenclatureRepository->get();
        $value = ($nomenclature->values() ?? [])[$familyCode] ?? null;
        Assert::eq($expectedValue, $value ?: 'undefined');
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
            generateIfEmpty: $no !== ' no',
        );

        try {
            ($this->updateNomenclatureValuesHandler)($command);
        } catch (ViolationsException $exception) {
            $this->violationsContext->setViolationsException($exception);
        }
    }

    /**
     * @Then /^the family nomenclature operator should be (?P<operator>.*)$/
     */
    public function theFamilyNomenclatureOperatorShouldBe(string $operator): void
    {
        Assert::eq($this->nomenclatureRepository->get()->operator(), $operator);
    }

    /**
     * @Then the family nomenclature value should be :value
     */
    public function theFamilyNomenclatureValueShouldBe(string $value): void
    {
        Assert::eq($this->nomenclatureRepository->get()->value(), \intval($value));
    }

    /**
     * @Then the family nomenclature generation if empty should be :generateIfEmpty
     */
    public function theFamilyNomenclatureGenerationIfEmptyShouldBe(string $generateIfEmpty): void
    {
        Assert::eq($this->nomenclatureRepository->get()->generateIfEmpty(), $generateIfEmpty === 'true');
    }
}
