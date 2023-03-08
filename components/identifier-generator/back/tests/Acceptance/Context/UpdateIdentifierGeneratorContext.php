<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateIdentifierGeneratorContext implements Context
{
    private ?ViolationsException $violations = null;
    public const DEFAULT_IDENTIFIER_GENERATOR_CODE = 'default';

    public function __construct(
        private UpdateGeneratorHandler $updateGeneratorHandler,
        private IdentifierGeneratorRepository $generatorRepository,
    ) {
    }

    /**
     * @Given the ':generatorCode' identifier generator
     */
    public function theIdentifierGenerator(string $generatorCode): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString($generatorCode),
            Conditions::fromArray([Enabled::fromBoolean(true)]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr_FR' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );
        $this->generatorRepository->save($identifierGenerator);
    }

    /**
     * @Then The identifier generator is updated in the repository
     */
    public function identifierGeneratorIsUpdatedInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq($identifierGenerator->delimiter()->asString(), 'updatedGenerator');
    }

    /**
     * @Then The identifier generator is updated without label in the repository
     */
    public function identifierGeneratorIsUpdatedWithoutLabelInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq($identifierGenerator->labelCollection()->normalize(), (object)[]);
    }

    /**
     * @Then /^I should get an error on update with message '(?P<message>[^']*)'$/
     */
    public function iShouldGetAnErrorOnUpdateWithMessage(string $message): void
    {
        Assert::notNull($this->violations);
        Assert::contains($this->violations->getMessage(), $message);
    }

    /**
     * @Then I should not get any update error
     */
    public function iShouldNotGetAnyUpdateError(): void
    {
        Assert::null($this->violations);
    }

    /**
     * @Then The identifier generator is updated in the repository and delimiter is null
     */
    public function theIdentifierGeneratorIsUpdatedInTheRepositoryAndDelimiterIsNull(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::null($identifierGenerator->delimiter()->asString());
    }

    /**
     * @Then The identifier generator is updated in the repository and text transformation is lowercase
     */
    public function theIdentifierGeneratorIsUpdatedInTheRepositoryAndTextTransformationIsLowercase(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq($identifierGenerator->textTransformation()->normalize(), TextTransformation::LOWERCASE);
    }

    /**
     * @When I update the identifier generator
     */
    public function iUpdateTheIdentifierGenerator(): void
    {
        $this->tryToUpdateGenerator();
    }

    /**
     * @When I try to update an unknown identifier generator
     */
    public function iTryToUpdateAnUnknownIdentifierGenerator(): void
    {
        $this->tryToUpdateGenerator(code: 'unknown');
    }

    /**
     * @When I try to update an identifier generator with an unknown property
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAnUnknownProperty(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'unknown', 'string' => 'abcdef']]);
    }

    /**
     * @When /^I try to update an identifier generator with target '(?P<target>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithTarget(string $target): void
    {
        $this->tryToUpdateGenerator(target: $target);
    }

    /**
     * @When I try to update an identifier generator with blank structure
     */
    public function iTryToUpdateAnIdentifierGeneratorWithBlankStructure(): void
    {
        $this->tryToUpdateGenerator(structure: []);
    }

    /**
     * @When /^I try to update an identifier generator with an auto number with '(?P<numberMin>[^']*)' as number min and '(?P<digitsMin>[^']*)' as min digits$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAnAutoNumberWithNumberMinAndDigitsMin(int $numberMin, int $digitsMin): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'auto_number', 'numberMin' => $numberMin, 'digitsMin' => $digitsMin]]);
    }

    /**
     * @When I update an identifier generator without label
     */
    public function iUpdateAnIdentifierGeneratorWithoutLabel(): void
    {
        $this->tryToUpdateGenerator(labels: []);
    }

    /**
     * @When /^I try to update an identifier generator with '(?P<locale>[^']*)' label '(?P<label>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithLabel(string $locale, string $label): void
    {
        $this->tryToUpdateGenerator(labels: [$locale => $label]);
    }

    /**
     * @When I try to update an identifier generator with too many properties in structure
     */
    public function iTryToUpdateAnIdentifierGeneratorWithTooManyPropertiesInStructure(): void
    {
        $this->tryToUpdateGenerator(structure: [
            ['type' => 'free_text', 'string' => 'abcdef1'],
            ['type' => 'free_text', 'string' => 'abcdef2'],
            ['type' => 'free_text', 'string' => 'abcdef3'],
            ['type' => 'free_text', 'string' => 'abcdef4'],
            ['type' => 'free_text', 'string' => 'abcdef5'],
            ['type' => 'free_text', 'string' => 'abcdef6'],
            ['type' => 'free_text', 'string' => 'abcdef7'],
            ['type' => 'free_text', 'string' => 'abcdef8'],
            ['type' => 'free_text', 'string' => 'abcdef9'],
            ['type' => 'free_text', 'string' => 'abcdef10'],
            ['type' => 'free_text', 'string' => 'abcdef11'],
            ['type' => 'free_text', 'string' => 'abcdef12'],
            ['type' => 'free_text', 'string' => 'abcdef13'],
            ['type' => 'free_text', 'string' => 'abcdef14'],
            ['type' => 'free_text', 'string' => 'abcdef15'],
            ['type' => 'free_text', 'string' => 'abcdef16'],
            ['type' => 'free_text', 'string' => 'abcdef17'],
            ['type' => 'free_text', 'string' => 'abcdef18'],
            ['type' => 'free_text', 'string' => 'abcdef19'],
            ['type' => 'free_text', 'string' => 'abcdef20'],
            ['type' => 'free_text', 'string' => 'abcdef21'],
        ]);
    }

    /**
     * @When I try to update an identifier generator with multiple auto number in structure
     */
    public function iTryToUpdateAnIdentifierGeneratorWithMultipleAutoNumberInStructure(): void
    {
        $this->tryToUpdateGenerator(structure: [
            ['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 3],
            ['type' => 'auto_number', 'numberMin' => 1, 'digitsMin' => 4],
        ]);
    }

    /**
     * @When /^I try to update an identifier generator with free text '(?P<freetextContent>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeText(string $freetextContent): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'free_text', 'string' => $freetextContent]]);
    }

    /**
     * @When I try to update an identifier generator with free text without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeTextWithoutRequiredField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'free_text']]);
    }

    /**
     * @When I try to update an identifier generator with free text with unknown field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeTextWithUnknownField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'free_text', 'unknown' => 'hello', 'string' => 'hey']]);
    }

    /**
     * @When I try to update an identifier generator with autoNumber number min negative
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberNumberMinNegative(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'auto_number', 'numberMin' => -2, 'digitsMin' => 3]]);
    }

    /**
     * @When I try to update an identifier generator with autoNumber without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberWithoutRequiredField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'auto_number', 'numberMin' => 4]]);
    }

    /**
     * @When I try to update an identifier generator with autoNumber digits min negative
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberDigitsMinNegative(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'auto_number', 'digitsMin' => -2, 'numberMin' => 4]]);
    }

    /**
     * @When I try to update an identifier generator with autoNumber digits min too big
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberDigitsMinTooBig(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'auto_number', 'digitsMin' => 22, 'numberMin' => 4]]);
    }

    /**
     * @When I try to update an identifier generator with family property without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFamilyPropertyWithoutRequiredField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family']]);
    }

    /**
     * @When /^I try to update an identifier generator with invalid family property$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithInvalidFamilyProperty(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family', 'process' => ['type' => 'no'], 'unknown' => '']]);
    }

    /**
     * @When I try to update an identifier generator with empty family process property
     */
    public function iTryToUpdateAnIdentifierGeneratorWithEmptyFamilyProcessProperty(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family', 'process' => []]]);
    }

    /**
     * @When /^I try to update an identifier generator with a family process with type (?P<type>[^']*) and operator (?P<operator>[^']*) and (?P<value>[^']*) as value$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFamilyProcessWithTypeAndOperatorAndValue($type, $operator, $value): void
    {
        $value = \json_decode($value);
        $defaultStructure = ['type' => 'family', 'process' => ['type' => $type, 'operator' => $operator, 'value' => $value]];
        if ($operator === 'undefined') {
            unset($defaultStructure['process']['operator']);
        }
        if ($value === 'undefined') {
            unset($defaultStructure['process']['value']);
        }
        $this->tryToUpdateGenerator(structure: [$defaultStructure]);
    }

    /**
     * @When I try to update an identifier generator with a family containing invalid truncate process
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAFamilyContainingInvalidTruncateProcess(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'family', 'process' => ['type' => 'truncate', 'operator' => '=', 'value' => '1', 'unknown' => '']]]);
    }

    /**
     * @When /^I try to update an identifier generator with delimiter '(?P<delimiter>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithDelimiter(string $delimiter): void
    {
        $this->tryToUpdateGenerator(delimiter: $delimiter);
    }

    /**
     * @When /^I (?:try to )?update an identifier generator with text transformation (?P<textTransformation>.+)$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithTextTransformation(string $textTransformation): void
    {
        $this->tryToUpdateGenerator(textTransformation: $textTransformation);
    }

    /**
     * @When I update the identifier generator with delimiter null
     */
    public function iUpdateTheIdentifierGeneratorWithDelimiterNull(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                'sku',
                null,
                'no',
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with unknown condition type
     */
    public function iTryToUpdateAnIdentifierGeneratorWithUnknownConditionType(): void
    {
        $this->tryToUpdateGenerator(conditions: [
            ['type' => 'unknown', 'value' => true],
        ]);
    }

    /**
     * @When /^I try to update an identifier generator with (\d+) conditions$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithConditions(string $count): void
    {
        $this->tryToUpdateGenerator(conditions: \array_fill(0, \intval($count), $this->getValidCondition('simple_select')));
    }

    /**
     * @When I try to update an identifier generator with :arg1 enabled conditions
     */
    public function iTryToUpdateAnIdentifierGeneratorWithEnabledConditions($arg1): void
    {
        $this->tryToUpdateGenerator(conditions: [
            ['type' => 'enabled', 'value' => true],
            ['type' => 'enabled', 'value' => true],
        ]);
    }

    /**
     * @When I try to update an identifier generator with 2 family conditions
     */
    public function iTryToUpdateAnIdentifierGeneratorWith2FamilyConditions(): void
    {
        $this->tryToUpdateGenerator(conditions: [
            ['type' => 'family', 'operator' => 'EMPTY'],
            ['type' => 'family', 'operator' => 'NOT EMPTY'],
        ]);
    }

    /**
     * @When I try to update an identifier generator with 2 category conditions
     */
    public function iTryToUpdateAnIdentifierGeneratorWith2CategoryConditions(): void
    {
        $this->tryToUpdateGenerator(conditions: [
            ['type' => 'category', 'operator' => 'IN', 'value' => ['tshirts']],
            ['type' => 'category', 'operator' => 'NOT IN', 'value' => ['shoes']],
        ]);
    }

    /**
     * @When /^I try to update an identifier generator \
     *     with an? (?P<type>simple_select|multi_select|family|enabled|category) condition\
     *     (?:(?: with| and|,) (?P<attributeCode>[^ ]*) attribute)?\
     *     (?:(?: with| and|,) (?P<operator>[^ ]*) operator)?\
     *     (?:(?: with| and|,) (?P<scope>[^ ]*) scope)?\
     *     (?:(?: with| and|,) (?P<locale>[^ ]*) locale)?\
     *     (?:(?: with| and|,) (?P<value>.*) as value)?\
     *     (?P<unknown>(?: with| and|,) an unknown property)?$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithCondition(
        string $type,
        string $attributeCode = '',
        string $operator = '',
        string $scope = '',
        string $locale = '',
        string $value = '',
        string $unknown = '',
    ): void {
        $defaultCondition = $this->getValidCondition($type);
        if ($attributeCode !== '') {
            $defaultCondition['attributeCode'] = $attributeCode;
        }
        if ('undefined' === $scope) {
            unset($defaultCondition['scope']);
        } elseif ('' !== $scope) {
            $defaultCondition['scope'] = $scope;
        }
        if ('undefined' === $locale) {
            unset($defaultCondition['locale']);
        } elseif ('' !== $locale) {
            $defaultCondition['locale'] = $locale;
        }
        if ('undefined' === $value) {
            unset($defaultCondition['value']);
        } elseif ($value !== '') {
            $defaultCondition['value'] = \json_decode($value);
        }
        if ('undefined' === $operator) {
            unset($defaultCondition['operator']);
        } elseif ($operator !== '') {
            $defaultCondition['operator'] = $operator;
        }
        if ($unknown !== '') {
            $defaultCondition['unknown'] = 'unknown property';
        }
        $this->tryToUpdateGenerator(conditions: [$defaultCondition]);
    }

    private function tryToUpdateGenerator(
        ?string $code = null,
        ?array $structure = null,
        ?array $conditions = null,
        ?array $labels = null,
        ?string $target = null,
        ?string $delimiter = null,
        ?string $textTransformation = null,
    ): void {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                $code ?? self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                $conditions ?? [
                    $this->getValidCondition('enabled'),
                    $this->getValidCondition('family', 'EMPTY'),
                    $this->getValidCondition('simple_select'),
                    $this->getValidCondition('multi_select'),
                    $this->getValidCondition('category'),
                ],
                $structure ?? [['type' => 'free_text', 'string' => self::DEFAULT_IDENTIFIER_GENERATOR_CODE]],
                $labels ?? ['fr_FR' => 'Générateur'],
                $target ?? 'sku',
                $delimiter ?? 'updatedGenerator',
                $textTransformation ?? 'no',
            ));
        } catch (ViolationsException $violations) {
            $this->violations = $violations;
        }
    }

    private function getValidCondition(string $type, ?string $operator = null): array
    {
        switch ($type) {
            case 'enabled':
                return [
                    'type' => 'enabled',
                    'value' => true,
                ];
            case 'family':
                $familyCondition = [
                    'type' => 'family',
                    'operator' => $operator ?? 'IN',
                ];
                if ('EMPTY' !== $operator) {
                    $familyCondition['value'] = ['tshirt'];
                }

                return $familyCondition;
            case 'simple_select':
                return [
                    'type' => 'simple_select',
                    'operator' => $operator ?? 'IN',
                    'attributeCode' => 'color',
                    'value' => ['green'],
                ];
            case 'multi_select':
                return [
                    'type' => 'multi_select',
                    'operator' => $operator ?? 'IN',
                    'attributeCode' => 'a_multi_select',
                    'value' => ['option_a', 'option_b'],
                ];
            case 'category':
                return [
                    'type' => 'category',
                    'operator' => $operator ?? 'IN',
                    'value' => ['tshirts'],
                ];
        }

        throw new \InvalidArgumentException('Unknown type ' . $type . ' for getValidCondition');
    }
}
