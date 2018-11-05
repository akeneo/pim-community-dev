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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ThereShouldBeLessRecordsThanLimitValidator extends ConstraintValidator
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var int */
    private $recordsLimit;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        int $recordsLimit
    ) {
        $this->recordRepository = $recordRepository;
        $this->recordsLimit = $recordsLimit;
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
        if (!$command instanceof CreateRecordCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    CreateRecordCommand::class,
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
        if (!$constraint instanceof ThereShouldBeLessRecordsThanLimit) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateRecordCommand $command): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier);
        $total = $this->recordRepository->countByReferenceEntity($referenceEntityIdentifier);

        if ($total >= $this->recordsLimit) {
            $this->context->buildViolation(ThereShouldBeLessRecordsThanLimit::ERROR_MESSAGE)
                ->setParameter('%record_label%', current($command->labels))
                ->setParameter('%limit%', $this->recordsLimit)
                ->atPath('labels')
                ->addViolation();
        }
    }
}
