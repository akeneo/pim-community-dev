<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\ViolationsException;
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
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateIdentifierGeneratorContext implements Context
{
    private ?ViolationsException $violations = null;

    public function __construct(
        private CreateGeneratorHandler $createGeneratorHandler,
        private IdentifierGeneratorRepository $generatorRepository,
        private SaverInterface $attributeRepository
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
     * @Then The identifier generator is saved in the repository
     */
    public function identifierGeneratorIsSavedInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get('abcdef');
        Assert::isInstanceOf($identifierGenerator, IdentifierGenerator::class);
    }

    /**
     * @Then the identifier generator is created
     */
    public function theIdentifierGeneratorIsCreated(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr_FR' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );
        $this->generatorRepository->save($identifierGenerator);
    }

    /**
     * @Then the identifier should not be created
     */
    public function theIdentifierShouldNotBeCreated(): void
    {
        Assert::null($this->generatorRepository->get('abcdef'));
    }

    /**
     * @Then I should not get any error
     */
    public function iShouldNotGetAnyError(): void
    {
        Assert::null($this->violations);
    }

    /**
     * @Then /^I should get an error with message '(?P<message>[^']*)'$/
     */
    public function iShouldGetAnErrorWithMessage(string $message): void
    {
        Assert::notNull($this->violations);
        Assert::contains($this->violations->getMessage(), $message);
    }

    /**
     * @When I create an identifier generator
     */
    public function iCreateAnIdentifierGenerator(): void
    {
        ($this->createGeneratorHandler)(new CreateGeneratorCommand(
            'abcdef',
            [],
            [['type' => 'free_text', 'string' => 'abcdef']],
            ['fr_FR' => 'Générateur'],
            'sku',
            '-'
        ));
    }

    /**
     * @When I try to create new identifier generator
     */
    public function iTryToCreateNewIdentifierGenerator(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When /^I try to create an identifier generator with target '(?P<target>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithTarget(string $target): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I try to create an identifier generator with blank structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithBlankStructure(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I try to create an identifier generator with an unknown property
     */
    public function iTryToCreateAnIdentifierGeneratorWithAnUnknownProperty(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I try to create an identifier generator with too many properties in structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithTooManyPropertiesInStructure(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I try to create an identifier generator with multiple auto number in structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithMultipleAutoNumberInStructure(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When /^I try to create an identifier generator with free text '(?P<freetextContent>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithFreeText(string $freetextContent): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I try to create an identifier generator with free text without required field
     */
    public function iCreateAnIdentifierGeneratorWithFreeTextWithoutRequiredField(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I try to create an identifier generator with free text with unknown field
     */
    public function iTryToCreateAnIdentifierGeneratorWithFreeTextWithUnknownField(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I try to create an identifier generator with autoNumber without required field
     */
    public function iTryToCreateAnIdentifierGeneratorWithAutonumberWithoutRequiredField(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When /^I try to create an identifier generator with an auto number with '(?P<numberMin>[^']*)' as number min and '(?P<digitsMin>[^']*)' as min digits$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithAnAutoNumberWithNumberMinAndDigitsMin(int $numberMin, int $digitsMin): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When I create an identifier generator without label
     */
    public function iCreateAnIdentifierGeneratorWithoutLabel(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When /^I try to create an identifier generator with '(?P<locale>[^']*)' label '(?P<label>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithLabel(string $locale, string $label): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
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
     * @When /^I try to create an identifier generator with delimiter '(?P<delimiter>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithDelimiter(string $delimiter): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                [],
                'sku',
                $delimiter
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I create an identifier generator with delimiter null
     */
    public function iCreateAnIdentifierGeneratorWithDelimiterNull(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
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
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                $code,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr_FR' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }
}
