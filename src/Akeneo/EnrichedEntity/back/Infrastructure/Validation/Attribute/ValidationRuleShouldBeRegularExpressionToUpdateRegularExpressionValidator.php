<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Attribute;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditValidationRuleCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ValidationRuleShouldBeRegularExpressionToUpdateRegularExpressionValidator extends ConstraintValidator
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
        if (!$this->isUpdatingRegularExpression($editAttributeCommand)) {
            return;
        }

        $attribute = $this->getAttribute($editAttributeCommand->identifier);
        if (!$attribute->isTextArea() && $attribute->isValidationRuleRegularExpression()) {
            return;
        }

        if ($attribute->isTextArea() && !$this->isTextAreaIsAlsoBeingUpdated($editAttributeCommand)) {
            $this->context->buildViolation(ValidationRuleShouldBeRegularExpressionToUpdateRegularExpression::ATTRIBUTE_SHOULD_BE_A_SIMPLE_TEXT)
                ->setParameters([
                    'attribute_code' => $editAttributeCommand->identifier['identifier'],
                    'enriched_entity_code' => $editAttributeCommand->identifier['enriched_entity_identifier']
                ])
                ->atPath('regularExpression')
                ->addViolation();
        }

        if (!$attribute->isTextArea() && !$this->isValidationRuleAlsoBeingUpdated($editAttributeCommand)) {
            $this->context->buildViolation(ValidationRuleShouldBeRegularExpressionToUpdateRegularExpression::WRONG_VALIDATION_RULE_TYPE)
                ->setParameters([
                    'attribute_code' => $editAttributeCommand->identifier['identifier'],
                    'enriched_entity_code' => $editAttributeCommand->identifier['enriched_entity_identifier']
                ])
                ->atPath('regularExpression')
                ->addViolation();
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof ValidationRuleShouldBeRegularExpressionToUpdateRegularExpression) {
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

    private function isUpdatingRegularExpression(EditAttributeCommand $editAttributeCommand): bool
    {
        foreach ($editAttributeCommand->editCommands as $command) {
            if ($command instanceof EditRegularExpressionCommand) {
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

    private function isTextAreaIsAlsoBeingUpdated(EditAttributeCommand $editAttributeCommand): bool
    {
        foreach ($editAttributeCommand->editCommands as $command) {
            if ($command instanceof EditValidationRuleCommand && AttributeValidationRule::REGULAR_EXPRESSION === $command->validationRule) {
                return true;
            }
        }

        return false;
    }

    private function isValidationRuleAlsoBeingUpdated($editAttributeCommand): bool
    {
        foreach ($editAttributeCommand->editCommands as $command) {
            if ($command instanceof EditValidationRuleCommand) {
                return true;
            }
        }

        return false;
    }
}
