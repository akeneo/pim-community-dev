<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateNomenclatureHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SimpleSelectNomenclatureContext implements Context
{
    private ?ViolationsException $violations = null;
    private const DEFAULT_OPERATOR = '<=';
    private const DEFAULT_VALUE = 3;
    private const DEFAULT_GENERATE_IF_EMPTY = false;

    public function __construct(
        private readonly SimpleSelectNomenclatureRepository $nomenclatureRepository,
        private readonly UpdateNomenclatureHandler $updateNomenclatureValuesHandler,
    ) {
    }

    /**
     * @Given a simple select nomenclature for :attributeCode with the following values
     */
    public function aSimpleSelectNomenclatureWithTheFollowingValues(string $attributeCode, TableNode $table): void
    {
        $nomenclatureValues = [];
        foreach ($table as $line) {
            $nomenclatureValues[$line['attributeOptionCode']] = $line['value'];
        }
        $command = new UpdateNomenclatureCommand(
            propertyCode: $attributeCode,
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            generateIfEmpty: self::DEFAULT_GENERATE_IF_EMPTY,
            values: $nomenclatureValues,
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When /^I (?:add|update) (.*) value for (.*) option of the (.*) simple select$/
     */
    public function iAddTheValueForSimpleSelect(string $value, string $attributeOptionCode, string $attributeCode): void
    {
        $command = new UpdateNomenclatureCommand(
            propertyCode: $attributeCode,
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            generateIfEmpty: self::DEFAULT_GENERATE_IF_EMPTY,
            values: [$attributeOptionCode => $value],
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @When I remove the :attributeOptionCode value from :attributeCode simple select nomenclature
     */
    public function iRemoveTheSimpleSelectValue(string $attributeOptionCode, string $attributeCode): void
    {
        $command = new UpdateNomenclatureCommand(
            propertyCode: $attributeCode,
            operator: self::DEFAULT_OPERATOR,
            value: self::DEFAULT_VALUE,
            generateIfEmpty: self::DEFAULT_GENERATE_IF_EMPTY,
            values: [$attributeOptionCode => null],
        );
        ($this->updateNomenclatureValuesHandler)($command);
    }

    /**
     * @Then The value for option :attributeOptionCode in :attributeCode simple select should not be defined
     */
    public function theValueForSimpleSelectShouldBeNull(string $attributeOptionCode, string $attributeCode): void
    {
        $nomenclature = $this->nomenclatureRepository->get($attributeCode);
        $nomenclatureValue = ($nomenclature->values() ?? [])[$attributeOptionCode] ?? null;
        Assert::null($nomenclatureValue);
    }

    /**
     * @Then The value for option :attributeOptionCode in :attributeCode simple select should be :value
     */
    public function theValueForSimpleSelectShouldBe(string $attributeOptionCode, string $attributeCode, string $value): void
    {
        $nomenclature = $this->nomenclatureRepository->get($attributeCode);
        $nomenclatureValue = ($nomenclature->values() ?? [])[$attributeOptionCode] ?? null;
        Assert::eq($value, 'undefined' === $nomenclatureValue ? null : $nomenclatureValue);
    }

    /**
     * @When /^I (create|update) the simple select nomenclature of attribute (?P<attributeCode>[^ ]*) operator to (?P<operator>[^ ]*), value to (?P<value>.*) and(?P<no> no)? generation if empty$/
     */
    public function iUpdateTheSimpleSelectNomenclatureOperatorTo(string $attributeCode, string $operator, string $value, string $no = ''): void
    {
        $command = new UpdateNomenclatureCommand(
            propertyCode: $attributeCode,
            operator: $operator,
            value: \intval($value),
            generateIfEmpty: $no !== ' no',
        );

        try {
            ($this->updateNomenclatureValuesHandler)($command);
        } catch (ViolationsException $e) {
            $this->violations = $e;
        }
    }

    /**
     * @Then /^the simple select nomenclature operator for (?P<attributeCode>.*) should be (?P<operator>.*)$/
     */
    public function theSimpleSelectNomenclatureOperatorShouldBe(string $attributeCode, string $operator): void
    {
        Assert::eq($this->nomenclatureRepository->get($attributeCode)->operator(), $operator);
    }

    /**
     * @Then the :attributeCode simple select nomenclature value should be :value
     */
    public function theSimpleSelectNomenclatureValueShouldBe(string $attributeCode, string $value): void
    {
        Assert::eq($this->nomenclatureRepository->get($attributeCode)->value(), \intval($value));
    }

    /**
     * @Then the simple select :attributeCode nomenclature generation if empty should be :generateIfEmpty
     */
    public function theSimpleSelectNomenclatureGenerationIfEmptyShouldBe(string $attributeCode, string $generateIfEmpty): void
    {
        Assert::eq($this->nomenclatureRepository->get($attributeCode)->generateIfEmpty(), $generateIfEmpty === 'true');
    }

    /**
     * @Then I should have a simple select nomenclature error :message
     */
    public function iShouldHaveAnError($message): void
    {
        Assert::notNull($this->violations, 'No error were raised.');
        Assert::contains($this->violations->getMessage(), $message);
    }

    /**
     * @Then I should not have an error for a simple select nomenclature
     */
    public function iShouldNotHaveAnError(): void
    {
        Assert::null($this->violations);
    }
}
