<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\MassEditAssetsCommand as MassEditAssetsCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class MassEditAssetsCommandValidator extends ConstraintValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($massEditAssetsCommand, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($massEditAssetsCommand);

        if (empty($massEditAssetsCommand->editValueCommands)) {
            $this->context
                ->buildViolation($constraint->emptyValueCommandMessage)
                ->addViolation();
        }

        $this->editValueCommandsAreUnique($constraint, $massEditAssetsCommand->editValueCommands);
        foreach ($massEditAssetsCommand->editValueCommands as $id => $command) {
            $violations = $this->validator->validate($command);
            foreach ($violations as $violation) {
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath(sprintf('updaters.%s', $id))
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural())
                    ->setInvalidValue($command)
                    ->addViolation();
            }
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof MassEditAssetsCommandConstraint) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command)
    {
        if (!$command instanceof MassEditAssetsCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    MassEditAssetsCommand::class,
                    get_class($command)
                )
            );
        }
    }

    /**
     * @param AbstractEditValueCommand[] $editValueCommands
     */
    private function editValueCommandsAreUnique(Constraint $constraint, array $editValueCommands): void
    {
        $uniqueEditValueContextCommands = [];
        foreach ($editValueCommands as $id => $editValueCommand) {
            $normalizedEditValueCommand = $editValueCommand->normalize();
            $editValueContextCommand = [
                'attribute' => $normalizedEditValueCommand['attribute'],
                'channel' => $normalizedEditValueCommand['channel'],
                'locale' => $normalizedEditValueCommand['locale'],
            ];

            if (in_array($editValueContextCommand, $uniqueEditValueContextCommands)) {
                $this->context->buildViolation($constraint->duplicatedUpdater)
                    ->atPath(sprintf('updaters.%s', $id))
                    ->addViolation();

                continue;
            }

            $uniqueEditValueContextCommands[] = $editValueContextCommand;
        }
    }
}
