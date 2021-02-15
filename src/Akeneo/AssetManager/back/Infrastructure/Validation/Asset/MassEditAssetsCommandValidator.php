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

use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class MassEditAssetsCommandValidator extends ConstraintValidator
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($massEditAssetsCommand, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($massEditAssetsCommand);
        $updaters = $massEditAssetsCommand->updaters;

        if (!$this->isArray($updaters)) {
            return;
        }

        foreach ($updaters as $updater) {
            $violations = $this->validator->validate($updater->command);
            foreach ($violations as $violation) {
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath(sprintf('updaters.%s', $updater['id']))
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural())
                    ->setInvalidValue($updater)
                    ->addViolation();
            }
        }
    }

    private function isArray($updaters): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($updaters, new Assert\Type('array'));
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
        if (!$constraint instanceof MassEditAssetsCommand) {
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
}
