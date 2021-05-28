<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsTextareaCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextPropertyUpdatesShouldBeCoherentValidator extends ConstraintValidator
{
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($editAttributeCommand, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($editAttributeCommand);

        $databaseAttribute = $this->getAttribute($editAttributeCommand->identifier);
        if (!$this->isAttributeTypeText($databaseAttribute)) {
            return;
        }

        if ($this->isTextareaIsSetToTrue($editAttributeCommand, $databaseAttribute)) {
            if ($this->isValidationRuleUpdatedToSomethingElseThanNone($editAttributeCommand)) {
                $this->buildViolationCannotUpdateValidationRuleToSomethingElseThanNone();
            }
            if ($this->isRegularExpressionNotEmpty($editAttributeCommand)) {
                $this->buildViolationCannotSetANonEmptyRegularExpression();
            }
        } else {
            if ($this->isRichTextEditorSetToTrue($editAttributeCommand)) {
                $this->buildViolationCannotSetRichTextEditorToTrue();
            }
            if (!$this->isValidationRuleSetToRegularExpression($editAttributeCommand, $databaseAttribute)
                && $this->isRegularExpressionNotEmpty($editAttributeCommand)
            ) {
                $this->buildViolationCannotUpdateRegularExpression();
            }
            if ($this->hasValidationRuleSetToRegularExpression($editAttributeCommand)
                && !$this->isRegularExpressionNotEmpty($editAttributeCommand)
            ) {
                $this->buildViolationRegularExpressionShouldNotBeEmpty();
            }
        }
    }

    private function checkConstraintType($constraint)
    {
        if (!$constraint instanceof TextPropertyUpdatesShouldBeCoherent) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($editAttributeCommand): void
    {
        if (!$editAttributeCommand instanceof EditAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                EditAttributeCommand::class, get_class($editAttributeCommand)));
        }
    }

    private function getAttribute(string $identifier): AbstractAttribute
    {
        return $this->attributeRepository->getByIdentifier(AttributeIdentifier::fromString($identifier));
    }


    private function isAttributeTypeText($databaseAttribute): bool
    {
        return $databaseAttribute instanceof TextAttribute;
    }

    private function isTextareaIsSetToTrue(EditAttributeCommand $editAttributeCommand, TextAttribute $databaseAttribute): bool
    {
        if ($this->hasTextareaSet($editAttributeCommand)) {
            $command = $editAttributeCommand->findCommand(EditIsTextareaCommand::class);
            if (null !== $command) {
                return $command->isTextarea;
            }
        }

        return $databaseAttribute->isTextarea();
    }

    private function hasTextareaSet(EditAttributeCommand $editAttributeCommand): bool
    {
        return null !== $editAttributeCommand->findCommand(EditIsTextareaCommand::class);
    }

    private function isValidationRuleUpdatedToSomethingElseThanNone(EditAttributeCommand $editAttributeCommand): bool
    {
        $command = $editAttributeCommand->findCommand(EditValidationRuleCommand::class);

        return null !== $command && AttributeValidationRule::NONE !== $command->validationRule;
    }

    private function isRichTextEditorSetToTrue(EditAttributeCommand $editAttributeCommand)
    {
        $command = $editAttributeCommand->findCommand(EditIsRichTextEditorCommand::class);

        return null !== $command && true === $command->isRichTextEditor;
    }

    private function isRegularExpressionNotEmpty(EditAttributeCommand $editAttributeCommand): bool
    {
        $command = $editAttributeCommand->findCommand(EditRegularExpressionCommand::class);

        return null !== $command && null !== $command->regularExpression;
    }

    private function hasValidationRuleSetToRegularExpression(EditAttributeCommand $editAttributeCommand): bool
    {
        $command = $editAttributeCommand->findCommand(EditValidationRuleCommand::class);

        return null !== $command
            && null !== $command->validationRule
            && AttributeValidationRule::REGULAR_EXPRESSION === $command->validationRule;
    }

    private function isValidationRuleSetToRegularExpression(
        EditAttributeCommand $editAttributeCommand,
        TextAttribute $databaseAttribute
    ): bool {
        if ($this->hasValidationRuleSetToRegularExpression($editAttributeCommand)) {
            return true;
        }

        return $databaseAttribute->isValidationRuleSetToRegularExpression();
    }

    private function buildViolationCannotUpdateValidationRuleToSomethingElseThanNone(): void
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::SHOULD_BE_A_SIMPLE_TEXT_TO_SET_VALIDATION_RULE_TO_SOMETHING_ELSE_THAN_NONE)
            ->addViolation();
    }

    private function buildViolationCannotSetANonEmptyRegularExpression(): void
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::CANNOT_SET_A_NON_EMPTY_REGULAR_EXPRESSION)->addViolation();
    }

    private function buildViolationCannotSetRichTextEditorToTrue(): void
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::SHOULD_BE_A_TEXT_AREA_TO_UPDATE_IS_RICH_TEXT_EDITOR)
            ->addViolation();
    }

    private function buildViolationCannotUpdateRegularExpression()
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::CANNOT_UPDATE_REGULAR_EXPRESSION)
            ->addViolation();
    }

    private function buildViolationRegularExpressionShouldNotBeEmpty(): void
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::REGULAR_EXPRESSION_SHOULD_NOT_BE_EMPTY)
            ->atPath('regularExpression')
            ->addViolation();
    }
}
