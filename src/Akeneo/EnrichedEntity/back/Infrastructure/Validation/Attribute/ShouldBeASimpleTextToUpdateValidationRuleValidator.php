<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsRichTextEditorCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextAreaCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks the attribute is a text area, if not checks if there is an update doing so in the list of updates.
 *
 * Otherwise, builds a violation.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ShouldBeASimpleTextToUpdateValidationRuleValidator extends ConstraintValidator
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
        if (!$this->isUpdatingValidationRule($editAttributeCommand)) {
            return;
        }

        $attribute = $this->getAttribute($editAttributeCommand->identifier);
        if ($this->hasValidationRuleToUpdate($editAttributeCommand) && !$attribute->isTextArea()) {
            return;
        }
        if ($this->hasValidationRuleToUpdate($editAttributeCommand) && $attribute->isTextArea() && !$this->isTextAreaIsAlsoUpdated($editAttributeCommand)) {
            $this->buildViolation($editAttributeCommand);
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof ShouldBeASimpleTextToUpdateValidationRule) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function checkCommandType($editAttributeCommand): void
    {
        if (!$editAttributeCommand instanceof EditAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                EditRegularExpressionCommand::class, get_class($editAttributeCommand)));
        }
    }

    private function isUpdatingValidationRule(EditAttributeCommand $editAttributeCommand): bool
    {
        foreach ($editAttributeCommand->editCommands as $command) {
            if ($command instanceof EditValidationRuleCommand) {
                return true;
            }
        }

        return false;
    }

    private function getAttribute(array $identifier): TextAttribute
    {
        return $this->attributeRepository->getByIdentifier(AttributeIdentifier::create(
            $identifier['enriched_entity_identifier'],
            $identifier['identifier'])
        );
    }

    private function isTextAreaIsAlsoUpdated(EditAttributeCommand $editAttributeCommand): bool
    {
        foreach ($editAttributeCommand->editCommands as $command) {
            if ($command instanceof EditIsTextAreaCommand) {
                return true;
            }
        }

        return false;
    }

    private function hasValidationRuleToUpdate($editAttributeCommand)
    {
        foreach ($editAttributeCommand->editCommands as $command) {
            if ($command instanceof EditValidationRuleCommand) {
                return true;
            }
        }

        return false;
    }

    private function buildViolation($editAttributeCommand): void
    {
        $this->context->buildViolation(ShouldBeASimpleTextToUpdateValidationRule::SHOULD_BE_A_SIMPLE_TEXT_TO_UPDATE_VALIDATION_RULE)
            ->setParameters([
                'attribute_code'       => $editAttributeCommand->identifier['identifier'],
                'enriched_entity_code' => $editAttributeCommand->identifier['enriched_entity_identifier']
            ])
            ->atPath('regularExpression')
            ->addViolation();
    }
}
