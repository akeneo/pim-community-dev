<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Record;

use Akeneo\EnrichedEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Query\Record\RecordExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RecordShouldExistValidator extends ConstraintValidator
{
    /** @var RecordExistsInterface */
    private $recordExists;

    public function __construct(RecordExistsInterface $recordExists)
    {
        $this->recordExists = $recordExists;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        $attributeExists = $this->recordExists->withEnrichedEntityAndCode(
            EnrichedEntityIdentifier::fromString($command->enrichedEntityIdentifier),
            RecordCode::fromString($command->recordCode)
        );

        if (!$attributeExists) {
            $this->context->buildViolation(RecordShouldExist::ERROR_MESSAGE)
                ->atPath('code')
                ->addViolation();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof DeleteRecordCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                DeleteRecordCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof RecordShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
