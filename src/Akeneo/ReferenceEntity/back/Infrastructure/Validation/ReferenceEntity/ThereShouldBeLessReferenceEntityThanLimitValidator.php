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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\ReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ThereShouldBeLessReferenceEntityThanLimitValidator extends ConstraintValidator
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var int */
    private $referenceEntityLimit;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        int $referenceEntityLimit
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->referenceEntityLimit = $referenceEntityLimit;
    }

    public function validate($command, Constraint $constraint): void
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
        if (!$command instanceof CreateReferenceEntityCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    CreateReferenceEntityCommand::class,
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
        if (!$constraint instanceof ThereShouldBeLessReferenceEntityThanLimit) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateReferenceEntityCommand $command): void
    {
        $total = $this->referenceEntityRepository->count();

        if ($total >= $this->referenceEntityLimit) {
            $this->context->buildViolation(ThereShouldBeLessReferenceEntityThanLimit::ERROR_MESSAGE)
                ->setParameter('%reference_entity_label%', current($command->labels))
                ->setParameter('%limit%', $this->referenceEntityLimit)
                ->atPath('labels')
                ->addViolation();
        }
    }
}
