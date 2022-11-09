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
    private ?\InvalidArgumentException $invalidArgumentException = null;

    public function __construct(
        private CreateGeneratorHandler $createGeneratorHandler,
        private IdentifierGeneratorRepository $generatorRepository,
        private SaverInterface $attributeRepository
    ) {
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
            ['fr' => 'Générateur'],
            'sku',
            '-'
        ));
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
     * @Given the :attributeCode identifier attribute
     */
    public function theIdentifierAttribute(string $attributeCode): void
    {
        $identifierAttribute = new Attribute();
        $identifierAttribute->setType(AttributeTypes::IDENTIFIER);
        $identifierAttribute->setCode($attributeCode);
        $identifierAttribute->setScopable(false);
        $identifierAttribute->setLocalizable(false);
        $identifierAttribute->setBackendType(AttributeTypes::BACKEND_TYPE_TEXT);
        $this->attributeRepository->save($identifierAttribute);
    }

    /**
     * @When I try to create an identifier generator with not existing target :target
     */
    public function iTryToCreateAnIdentifierGeneratorWithNotExistingTarget(string $target): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr' => 'Générateur'],
                $target,
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
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
     * @Given I should not get any error
     */
    public function iShouldNotGetAnyError(): void
    {
        Assert::null($this->violations);
    }

    /**
     * @Then I should get an exception with message ':message'
     */
    public function iShouldGetAnExceptionWithMessage($message): void
    {
        Assert::notNull($this->invalidArgumentException);
        Assert::contains($this->invalidArgumentException->getMessage(), $message);
    }

    /**
     * @Then the identifier should not be created
     */
    public function theIdentifierShouldNotBeCreated(): void
    {
        Assert::null($this->generatorRepository->get('abcdef'));
    }

    /**
     * @Given the :attributeCode text attribute
     */
    public function theOtherTextAttribute(string $attributeCode): void
    {
        $identifierAttribute = new Attribute();
        $identifierAttribute->setType(AttributeTypes::TEXT);
        $identifierAttribute->setCode($attributeCode);
        $identifierAttribute->setScopable(false);
        $identifierAttribute->setLocalizable(false);
        $identifierAttribute->setBackendType(AttributeTypes::BACKEND_TYPE_TEXT);
        $this->attributeRepository->save($identifierAttribute);
    }

    /**
     * @When I try to create an identifier generator with target ':target'
     */
    public function iTryToCreateAnIdentifierGeneratorWithTarget(string $target): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr' => 'Générateur'],
                $target,
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
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
                ['fr' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @Given the identifier generator is created
     */
    public function theIdentifierGeneratorIsCreated(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );
        $this->generatorRepository->save($identifierGenerator);
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
                ['fr' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to create an identifier generator with blank target
     */
    public function iTryToCreateAnIdentifierGeneratorWithBlankTarget(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr' => 'Générateur'],
                '',
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
                ['fr' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to create an identifier generator with blank code
     */
    public function iTryToCreateAnIdentifierGeneratorWithBlankCode(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                '',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr' => 'Générateur'],
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
     * @When I try to create an identifier generator with code ':code'
     */
    public function iTryToCreateAnIdentifierGeneratorWithCode(string $code): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                $code,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to create an identifier generator with ':locale' label ':label'
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
     * @When I try to create an identifier generator with delimiter ':delimiter'
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
     * @When I try to create an identifier generator with an empty delimiter
     */
    public function iTryToCreateAnIdentifierGeneratorWithAnEmptyDelimiter(): void
    {
        try {
            ($this->createGeneratorHandler)(new CreateGeneratorCommand(
                'abcdef',
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                [],
                'sku',
                ''
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
     * @When I try to create an identifier generator with too many properties in structure
     */
    public function iTryToCreateAnIdentifierGeneratorWithTooManyPropertiesInStructure()
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
                    ['type' => 'free_text', 'string' => 'abcdef21']
                ],
                ['fr' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }
}
