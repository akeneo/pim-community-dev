<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Email;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditTextValueCommand as EditTextValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditTextValueCommandValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof EditTextValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditTextValueCommand::class,
                    get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditTextValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditTextValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditTextValueCommand $command): void
    {
        $violations = $this->checkType($command);
        if (0 === $violations->count()) {
            $violations->addAll($this->checkTextLength($command));
            $violations->addAll($this->checkValidationRule($command));
        }

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath((string) $command->attribute->getCode())
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural())
                    ->setInvalidValue($violation->getInvalidValue())
                    ->addViolation();
            }
        }
    }

    private function checkType(EditTextValueCommand $command):ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        return $validator->validate($command->text, new Type('string'));
    }

    private function checkTextLength(EditTextValueCommand $command)
    : ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        return $validator->validate($command->text, [
            new Length([
                'min' => 0,
                'max' => $command->attribute->getMaxLength()->intValue(),
            ]),
        ]);
    }

    private function checkValidationRule(EditTextValueCommand $command)
    : ConstraintViolationListInterface
    {
        if ($command->attribute->hasValidationRule()) {
            return new ConstraintViolationList();
        }

        $validator = Validation::createValidator();
        if ($command->attribute->isValidationRuleSetToRegularExpression()) {
            $attribute = $command->attribute;
            return $validator->validate($command->text, [
                new Callback(function ($value, ExecutionContextInterface $context, $payload) use ($attribute) {
                    if (!preg_match_all((string) $attribute->getRegularExpression(), $value)) {
                        return $this->context
                            ->buildViolation(EditTextValueCommandConstraint::TEXT_INCOMPATIBLE_WITH_REGULAR_EXPRESSION)
                            ->setParameter('%regular_expression%', (string) $attribute->getRegularExpression())
                            ->atPath((string) $attribute->getCode())
                            ->addViolation();
                    }
                }),
            ]);
        }

        if ($command->attribute->isValidationRuleSetToUrl()) {
            return $validator->validate($command->text, [new Url()]);
        }

        if ($command->attribute->isValidationRuleSetToEmail()) {
            return $validator->validate($command->text, [new Email()]);
        }

        return new ConstraintViolationList();
    }
}
