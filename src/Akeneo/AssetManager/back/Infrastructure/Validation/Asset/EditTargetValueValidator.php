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

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\CheckIfTransformationTarget;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EditTargetValueValidator extends ConstraintValidator
{
    private CheckIfTransformationTarget $checkIfTransformationTarget;

    public function __construct(CheckIfTransformationTarget $checkIfTransformationTarget)
    {
        $this->checkIfTransformationTarget = $checkIfTransformationTarget;
    }

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
        if (!$command instanceof AbstractEditValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', AbstractEditValueCommand::class,
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
        if (!$constraint instanceof EditTargetValue) {
            throw new UnexpectedTypeException($constraint, EditTargetValue::class);
        }
    }

    private function validateCommand(AbstractEditValueCommand $command): void
    {
        if ($this->checkIfTransformationTarget->forAttribute($command->attribute, $command->locale, $command->channel)) {
            $this->context->buildViolation(EditTargetValue::TARGET_READONLY)
                ->atPath((string) $command->attribute->getCode())
                ->setParameter('%attribute_code%', (string) $command->attribute->getCode())
                ->addViolation();
        }
    }
}
