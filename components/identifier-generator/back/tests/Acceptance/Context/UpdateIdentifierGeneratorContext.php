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
    private ?\InvalidArgumentException $invalidArgumentException = null;
    public const DEFAULT_IDENTIFIER_GENERATOR_CODE = 'default';

    public function __construct(
        private UpdateGeneratorHandler $updateGeneratorHandler,
        private IdentifierGeneratorRepository $generatorRepository,
    ) {
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
                ['fr' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (\InvalidArgumentException $exception) {
            $this->invalidArgumentException = $exception;
        } catch (ViolationsException $violations) {
            $this->violations = $violations;
        }
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
                ['fr' => 'Générateur'],
                'sku',
                'updatedGenerator'
            ));
        } catch (\InvalidArgumentException $exception) {
            $this->invalidArgumentException = $exception;
        } catch (ViolationsException $violations) {
            $this->violations = $violations;
        }
    }

    /**
     * @Given an existing identifier generator
     */
    public function anExistingIdentifierGenerator(): void
    {
        $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
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
     * @Given the :code identifier generator
     */
    public function theDefaultIdentifierGenerator(string $code): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString($code),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );
        $this->generatorRepository->save($identifierGenerator);
    }

    /**
     * @Then /^I should get an error message '(?P<message>[^']*)'$/
     */
    public function iShouldGetAnErrorMessage(string $message): void
    {
        Assert::notNull($this->violations);
        Assert::contains($this->violations->getMessage(), $message);
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
                ['fr' => 'Générateur'],
                'sku',
                '-'
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @Then /^I should get an exception message '(?P<message>[^']*)'$/
     */
    public function iShouldGetAnExceptionMessage($message): void
    {
        Assert::notNull($this->invalidArgumentException);
        Assert::contains($this->invalidArgumentException->getMessage(), $message);
    }

    /**
     * @When I try to update an identifier generator with target :target
     */
    public function iTryToUpdateAnIdentifierGeneratorWithTarget(string $target): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
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
     * @When I try to update an identifier generator with delimiter :delimiter
     */
    public function iTryToUpdateAnIdentifierGeneratorWithDelimiter(string $delimiter): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr' => 'Générateur'],
                'sku',
                $delimiter,
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @When I try to update an identifier generator with an empty delimiter
     */
    public function iTryToUpdateAnIdentifierGeneratorWithAnEmptyDelimiter(): void
    {
        try {
            ($this->updateGeneratorHandler)(new UpdateGeneratorCommand(
                self::DEFAULT_IDENTIFIER_GENERATOR_CODE,
                [],
                [['type' => 'free_text', 'string' => 'abcdef']],
                ['fr' => 'Générateur'],
                'sku',
                ''
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
                ['fr' => 'Générateur'],
                'sku',
                null,
            ));
        } catch (ViolationsException $exception) {
            $this->violations = $exception;
        }
    }

    /**
     * @Then The identifier generator is updated in the repository and delimiter is null
     */
    public function theIdentifierGeneratorIsUpdatedInTheRepositoryAndDelimiterIsNull(): void
    {
        $identifierGenerator = $this->generatorRepository->get(self::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq(null, $identifierGenerator->delimiter()->asString());
    }
}
