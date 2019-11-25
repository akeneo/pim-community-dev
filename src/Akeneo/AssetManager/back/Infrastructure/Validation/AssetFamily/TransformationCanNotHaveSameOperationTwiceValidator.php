<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransformationCanNotHaveSameOperationTwiceValidator extends ConstraintValidator
{
    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command);
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof TransformationCanNotHaveSameOperationTwice) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof CreateAssetFamilyCommand && !$command instanceof EditAssetFamilyCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s" or "%s", "%s" given',
                    CreateAssetFamilyCommand::class,
                    EditAssetFamilyCommand::class,
                    get_class($command)
                )
            );
        }
    }

    private function validateCommand($command): void
    {
        foreach ($command->transformations as $transformation) {
            $definedOperationTypes = [];
            foreach ($transformation['operations'] as $operation) {
                if (in_array($operation['type'], $definedOperationTypes)) {
                    $this->context->buildViolation(TransformationCanNotHaveSameOperationTwice::ERROR_MESSAGE)
                        ->setParameter('%asset_family_identifier%', $command->identifier)
                        ->setParameter('%operation_type%', $operation['type'])
                        ->atPath('transformations')
                        ->addViolation();
                }

                $definedOperationTypes[] = $operation['type'];
            }
        }
    }
}
