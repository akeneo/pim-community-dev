<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextAreaCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextPropertyUpdatesShouldBeCoherentValidator extends ConstraintValidator
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate($editAttributeCommand, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($editAttributeCommand);

        $databaseAttribute = $this->getAttribute($editAttributeCommand);
        if (!$this->isAttributeTypeText($databaseAttribute)) {
            return;
        }

        if ($this->isTextAreaIsSetToTrue($editAttributeCommand, $databaseAttribute)) {
            if ($this->isValidationRuleUpdatedToSomethingElseThanNone($editAttributeCommand)) {
                $this->buildViolationCannotUpdateValidationRuleToSomethingElseThanNone($editAttributeCommand);
            }
            if ($this->isRegularExpressionNotEmpty($editAttributeCommand)) {
                $this->buildViolationCannotSetANonEmptyRegularExpression($editAttributeCommand);
            }
        } else {
            if ($this->isRichTextEditorSetToTrue($editAttributeCommand)) {
                $this->buildViolationCannotSetRichTextEditorToTrue($editAttributeCommand);
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

    private function getAttribute(array $identifier): TextAttribute
    {
        return $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            $identifier['enriched_entity_identifier'],
            $identifier['identifier'])
        );
    }


    private function isAttributeTypeText($databaseAttribute): bool
    {
        return $databaseAttribute instanceof TextAttribute;
    }

    private function isTextAreaIsSetToTrue(EditAttributeCommand $editAttributeCommand, TextAttribute $databaseAttribute): bool
    {
        if ($this->hasTextAreaSet($editAttributeCommand)) {
            $command = $editAttributeCommand->getCommand(EditIsTextAreaCommand::class);
            if (null !== $command) {
                return $command->isTextArea;
            }
        }

        return $databaseAttribute->isTextArea();
    }

    private function hasTextAreaSet(EditAttributeCommand $editAttributeCommand): bool
    {
        return null !== $editAttributeCommand->getCommand(EditIsTextAreaCommand::class);
    }

    private function isValidationRuleUpdatedToSomethingElseThanNone(EditAttributeCommand $editAttributeCommand): bool
    {
        $command = $editAttributeCommand->getCommand(EditValidationRuleCommand::class);

        return null !== $command && null !== $command->validationRule;
    }

    private function isRichTextEditorSetToTrue(EditAttributeCommand $editAttributeCommand)
    {
        $command = $editAttributeCommand->getCommand(EditIsRichTextEditorCommand::class);

        return null !== $command && true === $command->isRichTextEditor;
    }

    private function isRegularExpressionNotEmpty(EditAttributeCommand $editAttributeCommand): bool
    {
        $command = $editAttributeCommand->getCommand(EditRegularExpressionCommand::class);

        return null !== $command && null !== $command->validationRule;
    }

    private function buildViolationCannotUpdateValidationRuleToSomethingElseThanNone($editAttributeCommand): void
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::SHOULD_BE_A_SIMPLE_TEXT_TO_SET_VALIDATION_RULE_TO_SOMETHING_ELSE_THAN_NONE)
            ->addViolation();
    }

    private function buildViolationCannotSetANonEmptyRegularExpression($editAttributeCommand): void
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::CANNOT_SET_A_NON_EMPTY_REGULAR_EXPRESSION)->addViolation();
    }

    private function buildViolationCannotSetRichTextEditorToTrue($editAttributeCommand): void
    {
        $this->context->buildViolation(TextPropertyUpdatesShouldBeCoherent::SHOULD_BE_A_TEXT_AREA_TO_UPDATE_IS_RICH_TEXT_EDITOR)
            ->addViolation();
    }
}
