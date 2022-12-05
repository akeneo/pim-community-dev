<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\UpdateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
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
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr_FR' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );
        $this->generatorRepository->save($identifierGenerator);
    }

    /**
     * @Then The identifier generator is updated in the repository
     */
    public function identifierGeneratorIsUpdatedInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq('updatedGenerator', $identifierGenerator->delimiter()->asString());
    }

    /**
     * @Then The identifier generator is updated without label in the repository
     */
    public function identifierGeneratorIsUpdatedWithoutLabelInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq((object) [], $identifierGenerator->labelCollection()->normalize());
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
        Assert::eq(null, $identifierGenerator->delimiter()->asString());
    }

    /**
     * @When I update the identifier generator
     */
    public function iUpdateTheIdentifierGenerator(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                'sku',
                'updatedGenerator'
            ));
        } catch (ViolationsException $violations) {
            $this->violations = $violations;
        }
    }

    /**
     * @When I try to update an unknown identifier generator
     */
    public function iTryToUpdateAnUnknownIdentifierGenerator(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                'unknown',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $violations) {
            $this->violations = $violations;
        }
    }

    /**
     * @When I try to update an identifier generator with an unknown property
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAnUnknownProperty(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'unknown', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When /^I try to update an identifier generator with target '(?P<target>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithTarget(string $target): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                $target,
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with blank structure
     */
    public function iTryToUpdateAnIdentifierGeneratorWithBlankStructure(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When /^I try to update an identifier generator with an auto number with '(?P<numberMin>[^']*)' as number min and '(?P<digitsMin>[^']*)' as min digits$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAnAutoNumberWithNumberMinAndDigitsMin(int $numberMin, int $digitsMin): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'auto_number', 'numberMin' => $numberMin, 'digitsMin' => $digitsMin]],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I update an identifier generator without label
     */
    public function iUpdateAnIdentifierGeneratorWithoutLabel(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                [],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When /^I try to update an identifier generator with '(?P<locale>[^']*)' label '(?P<label>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithLabel(string $locale, string $label): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                [$locale => $label],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with too many properties in structure
     */
    public function iTryToUpdateAnIdentifierGeneratorWithTooManyPropertiesInStructure(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [
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
                ],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with multiple auto number in structure
     */
    public function iTryToUpdateAnIdentifierGeneratorWithMultipleAutoNumberInStructure(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [
                    ['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 3],
                    ['type' => 'auto_number', 'numberMin' => 1, 'digitsMin' => 4],
                ],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When /^I try to update an identifier generator with free text '(?P<freetextContent>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeText(string $freetextContent): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => $freetextContent]],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with free text without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeTextWithoutRequiredField(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text']],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with free text with unknown field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeTextWithUnknownField(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'unknown' => 'hello', 'string' => 'hey']],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with autoNumber number min negative
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberNumberMinNegative(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'auto_number', 'numberMin' => -2, 'digitsMin' => 3]],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with autoNumber without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberWithoutRequiredField(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'auto_number', 'numberMin' => 4]],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with autoNumber digits min negative
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberDigitsMinNegative(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'auto_number', 'digitsMin' => -2, 'numberMin' => 4]],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with autoNumber digits min too big
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAutonumberDigitsMinTooBig(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'auto_number', 'digitsMin' => 22, 'numberMin' => 4]],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When /^I try to update an identifier generator with delimiter '(?P<delimiter>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithDelimiter(string $delimiter): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                'sku',
                $delimiter
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
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
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }
}
