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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetValueCommandsValidator extends ConstraintValidator
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($editAssetCommand, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($editAssetCommand);
        $editAssetValueCommands = $editAssetCommand->editAssetValueCommands;

        if (!$this->isArray($editAssetValueCommands)) {
            return;
        }

        foreach ($editAssetValueCommands as $editValueCommand) {
            $violations = $this->validator->validate($editValueCommand);
            foreach ($violations as $violation) {
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath(sprintf('values.%s', $violation->getPropertyPath()))
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural())
                    ->setInvalidValue($editValueCommand)
                    ->addViolation();
            }
        }
    }

    private function isArray($editAssetValueCommands): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($editAssetValueCommands, new Assert\Type('array'));
        $hasViolations = $violations->count() > 0;

        if ($hasViolations) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }

        return !$hasViolations;
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditAssetValueCommands) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command)
    {
        if (!$command instanceof EditAssetCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    EditTextValueCommand::class,
                    get_class($command)
                )
            );
        }
    }
}
