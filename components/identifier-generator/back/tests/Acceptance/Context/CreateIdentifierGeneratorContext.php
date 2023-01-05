<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateIdentifierGeneratorContext implements Context
{
    public const DEFAULT_CODE = 'abcdef';
    private ?ViolationsException $violations = null;

    public function __construct(
        private readonly CreateGeneratorHandler $createGeneratorHandler,
        private readonly IdentifierGeneratorRepository $generatorRepository,
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly AttributeOptionRepositoryInterface $attributeOptionRepository,
        private readonly FindFamilyCodes $findFamilyCodes,
    ) {
    }

    /**
     * @Given the ':attributeCode' attribute of type ':attributeType'
     */
    public function theAttribute(string $attributeCode, string $attributeType): void
    {
        $identifierAttribute = new Attribute();
        $identifierAttribute->setType($attributeType);
        $identifierAttribute->setCode($attributeCode);
        $identifierAttribute->setScopable(false);
        $identifierAttribute->setLocalizable(false);
        $identifierAttribute->setBackendType(AttributeTypes::BACKEND_TYPE_TEXT);
        $this->attributeRepository->save($identifierAttribute);
    }

    /**
     * @Given the :familyCode family
     */
    public function theFamily(string $familyCode)
    {
        $this->findFamilyCodes->save($familyCode);
    }

    /**
     * @Given /^the (?P<options>(('.*')(, | and )?)+) options? for '(?P<attributeCode>[^']*)' attribute$/
     */
    public function theAndOptionsForAttribute($optionCodes, $attributeCode)
    {
        $optionsCodesWithQuotes = \preg_split('/(, )|( and )/', $optionCodes);
        $optionsCodes = \array_map(
            static fn (string $optionWithQuotes): string => substr($optionWithQuotes, 1, -1),
            $optionsCodesWithQuotes
        );

        foreach ($optionsCodes as $optionCode) {
            $attributeOption = new AttributeOption();
            $attributeOption->setCode($optionCode);
            $attributeOption->setAttribute($this->attributeRepository->findOneByIdentifier($attributeCode));

            $this->attributeOptionRepository->save($attributeOption);
        }
    }

    /**
     * @Then The identifier generator is saved in the repository
     */
    public function identifierGeneratorIsSavedInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_CODE);
        Assert::isInstanceOf($identifierGenerator, IdentifierGenerator::class);
    }

    /**
     * @Then the identifier should not be created
     */
    public function theIdentifierShouldNotBeCreated(): void
    {
        Assert::null($this->generatorRepository->get(self::DEFAULT_CODE));
    }

    /**
     * @Then I should not get any error
     */
    public function iShouldNotGetAnyError(): void
    {
        Assert::null($this->violations, 'Errors were raised: ' . \json_encode($this->violations?->normalize()));
    }

    /**
     * @Then /^I should get an error with message '(?P<message>[^']*)'$/
     */
    public function iShouldGetAnErrorWithMessage(string $message): void
    {
        Assert::notNull($this->violations, 'No error were raised.');
        Assert::contains($this->violations->getMessage(), $message);
    }

    /**
     * @When I create an identifier generator
     */
    public function iCreateAnIdentifierGenerator(): void
    {
        $this->tryToCreateGenerator();
    }

    /**
     * @When /^I try to create an identifier generator with target '(?P<target>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithTarget(string $target): void
    {
        $this->tryToCreateGenerator(target: $target);
    }

    /**
     * @When I try to create an identifier generator with blank structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithBlankStructure(): void
    {
        $this->tryToCreateGenerator(structure: []);
    }

    /**
     * @When I try to create an identifier generator with an unknown property
     */
    public function iTryToCreateAnIdentifierGeneratorWithAnUnknownProperty(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'unknown', 'string' => 'a_string']]);
    }

    /**
     * @When I try to create an identifier generator with too many properties in structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithTooManyPropertiesInStructure(): void
    {
        $this->tryToCreateGenerator(
            structure:
            \array_fill(0, 21, ['type' => 'free_text', 'string' => 'abcdef1'])
        );
    }

    /**
     * @When I try to create an identifier generator with multiple auto number in structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithMultipleAutoNumberInStructure(): void
    {
        $this->tryToCreateGenerator(structure: [
            ['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 3],
            ['type' => 'auto_number', 'numberMin' => 1, 'digitsMin' => 4],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with free text '(?P<freetextContent>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithFreeText(string $freetextContent): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'free_text', 'string' => $freetextContent]]);
    }

    /**
     * @When I try to create an identifier generator with free text without required field
     */
    public function iCreateAnIdentifierGeneratorWithFreeTextWithoutRequiredField(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'free_text']]);
    }

    /**
     * @When I try to create an identifier generator with free text with unknown field
     */
    public function iTryToCreateAnIdentifierGeneratorWithFreeTextWithUnknownField(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'free_text', 'unknown' => 'hello', 'string' => 'hey']]);
    }

    /**
     * @When I try to create an identifier generator with autoNumber without required field
     */
    public function iTryToCreateAnIdentifierGeneratorWithAutonumberWithoutRequiredField(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'auto_number', 'numberMin' => 4]]);
    }

    /**
     * @When /^I try to create an identifier generator with an auto number with '(?P<numberMin>[^']*)' as number min and '(?P<digitsMin>[^']*)' as min digits$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithAnAutoNumberWithNumberMinAndDigitsMin(int $numberMin, int $digitsMin): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'auto_number', 'numberMin' => $numberMin, 'digitsMin' => $digitsMin]]);
    }

    /**
     * @When I create an identifier generator without label
     */
    public function iCreateAnIdentifierGeneratorWithoutLabel(): void
    {
        $this->tryToCreateGenerator(labels: []);
    }

    /**
     * @When /^I try to create an identifier generator with '(?P<locale>[^']*)' label '(?P<label>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithLabel(string $locale, string $label): void
    {
        $this->tryToCreateGenerator(labels: [$locale => $label]);
    }

    /**
     * @When /^I try to create an identifier generator with delimiter '(?P<delimiter>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithDelimiter(string $delimiter): void
    {
        $this->tryToCreateGenerator(delimiter: $delimiter);
    }

    /**
     * @When I create an identifier generator with delimiter null
     */
    public function iCreateAnIdentifierGeneratorWithDelimiterNull(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                self::DEFAULT_CODE,
                [],
                [['type' => 'free_text', 'string' => self::DEFAULT_CODE]],
                [],
                'sku',
                null,
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When /^I try to create an identifier generator with code '(?P<code>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithCode(string $code): void
    {
        $this->tryToCreateGenerator(code: $code);
    }

    /**
     * @When /^I try to create an identifier generator with unknown condition type$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithUnknownConditionType(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'unknown', 'value' => true],
        ]);
    }

    /**
     * @When I try to create an identifier generator with enabled condition without value
     */
    public function iTryToCreateAnIdentifierGeneratorWithEnabledConditionWithoutValue(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'enabled'],
        ]);
    }

    /**
     * @When I try to create an identifier generator with enabled condition with string value
     */
    public function iCreateAnIdentifierGeneratorWithEnabledConditionWithStringValue(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'enabled', 'value' => 'true'],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with (?P<type>enabled|simple_select) condition with an unknown property$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithEnabledConditionWithAnUnknownProperty($type): void
    {
        $defaultValue = $this->getValidCondition($type);
        $defaultValue['unknown'] = 'unknown property';

        $this->tryToCreateGenerator(conditions: [$defaultValue]);
    }

    /**
     * @When I try to create an identifier generator with 2 enabled conditions
     */
    public function iTryToCreateAnIdentifierGeneratorWithEnabledConditions(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'enabled', 'value' => true],
            ['type' => 'enabled', 'value' => true],
        ]);
    }

    /**
     * @When I try to create an identifier generator with a family condition with an unknown operator
     */
    public function iTryToCreateAnIdentifierGeneratorWithAFamilyConditionWithAnUnknownOperator(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'family', 'operator' => 'unknown'],
        ]);
    }

    /**
     * @When I try to create an identifier generator with a family condition with unknown property
     */
    public function iTryToCreateAnIdentifierGeneratorWithAFamilyConditionWithUnknownProperty(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'family', 'operator' => 'EMPTY', 'unknown' => 'unknown_field'],
        ]);
    }

    /**
     * @When I try to create an identifier generator with 2 family conditions
     */
    public function iTryToCreateAnIdentifierGeneratorWith2FamilyConditions(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'family', 'operator' => 'EMPTY'],
            ['type' => 'family', 'operator' => 'NOT EMPTY'],
        ]);
    }

    /**
     * @When I try to create an identifier generator with a family without operator
     */
    public function iTryToCreateAnIdentifierGeneratorWithAFamilyWithoutOperator(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'family', 'value' => ['shirts']],
        ]);
    }

    /**
     * @When /^I try to create an identifier generator with a (?P<type>family|simple_select) condition with operator (?P<operator>[^']*) and ((?P<value>[^']*) as value)$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithAFamilyConditionWithOperatorEmptyAndAsValue($type, $operator, $value): void
    {
        $defaultCondition = $this->getValidCondition($type, operator: $operator);

        if ($value === 'undefined') {
            unset($defaultCondition['value']);
            $this->tryToCreateGenerator(conditions: [$defaultCondition]);
        } else {
            $defaultCondition['value'] = \json_decode($value);
            $this->tryToCreateGenerator(conditions: [$defaultCondition]);
        }
    }

    /**
     * @When I try to create an identifier generator with a simple_select condition with :attributeCode attribute
     */
    public function iTryToCreateAnIdentifierGeneratorWithASimpleSelectConditionWithNameAttribute(string $attributeCode): void
    {
        $defaultCondition = $this->getValidCondition('simple_select');
        $defaultCondition['attributeCode'] = $attributeCode;
        $this->tryToCreateGenerator(conditions: [$defaultCondition]);
    }

    private function tryToCreateGenerator(
        ?string $code = null,
        ?array $structure = null,
        ?array $conditions = null,
        ?array $labels = null,
        ?string $target = null,
        ?string $delimiter = null,
    ): void {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                $code ?? self::DEFAULT_CODE,
                $conditions ?? [
                    $this->getValidCondition('enabled'),
                    $this->getValidCondition('family'),
                    $this->getValidCondition('simple_select'),
                ],
                $structure ?? [['type' => 'free_text', 'string' => self::DEFAULT_CODE]],
                $labels ?? ['fr_FR' => 'Générateur'],
                $target ?? 'sku',
                $delimiter ?? '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    private function getValidCondition(string $type, ?string $operator = null): array
    {
        switch($type) {
            case 'enabled': return [
                'type' => 'enabled',
                'value' => true
            ];
            case 'family': return [
                'type' => 'family',
                'operator' => $operator ?? 'IN',
                'value' => ['tshirt']
            ];
            case 'simple_select': return [
                'type' => 'simple_select',
                'operator' => $operator ?? 'IN',
                'attributeCode' => 'color',
                'value' => ['green'],
                'locale' => 'en_US',
                'scope' => 'ecommerce',
            ];
        }

        throw new \InvalidArgumentException('Unknown type ' . $type . ' for getValidCondition');
    }
}
