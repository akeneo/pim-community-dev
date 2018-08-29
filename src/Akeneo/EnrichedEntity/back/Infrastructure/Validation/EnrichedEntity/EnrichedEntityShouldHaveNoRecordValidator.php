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

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\EnrichedEntity;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity\DeleteEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityHasRecordsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EnrichedEntityShouldHaveNoRecordValidator extends ConstraintValidator
{
    /** @var EnrichedEntityHasRecordsInterface */
    private $enrichedEntityHasRecords;

    public function __construct(EnrichedEntityHasRecordsInterface $enrichedEntityHasRecords)
    {
        $this->enrichedEntityHasRecords = $enrichedEntityHasRecords;
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
        if (!$command instanceof DeleteEnrichedEntityCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    DeleteEnrichedEntityCommand::class,
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
        if (!$constraint instanceof EnrichedEntityShouldHaveNoRecord) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(DeleteEnrichedEntityCommand $command): void
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($command->identifier);
        $hasRecords = ($this->enrichedEntityHasRecords)($enrichedEntityIdentifier);

        if ($hasRecords) {
            $this->context->buildViolation(EnrichedEntityShouldHaveNoRecord::ERROR_MESSAGE)
                ->setParameter('%enriched_entity_identifier%', $enrichedEntityIdentifier)
                ->addViolation();
        }
    }
}
