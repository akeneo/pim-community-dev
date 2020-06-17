<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\RecordIsUsedAsProductVariantAxisInterface;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class RecordShouldNotBeUsedAsProductVariantAxisValidator extends ConstraintValidator
{
    /** @var RecordIsUsedAsProductVariantAxisInterface */
    private $recordIsUsedAsProductVariantAxis;

    public function __construct(
        RecordIsUsedAsProductVariantAxisInterface $recordIsUsedAsProductVariantAxis
    ) {
        $this->recordIsUsedAsProductVariantAxis = $recordIsUsedAsProductVariantAxis;
    }

    public function validate($command, Constraint $constraint): void
    {
        if (!$constraint instanceof RecordShouldNotBeUsedAsProductVariantAxis) {
            throw new UnexpectedTypeException($constraint, RecordShouldNotBeUsedAsProductVariantAxis::class);
        }

        if (!$command instanceof DeleteRecordCommand) {
            throw new UnexpectedTypeException($command, DeleteRecordCommand::class);
        }

        $recordCode = RecordCode::fromString($command->recordCode);
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier);

        $recordIsUsedAsProductVariantAxis = $this->recordIsUsedAsProductVariantAxis->execute(
            $recordCode,
            $referenceEntityIdentifier
        );

        if ($recordIsUsedAsProductVariantAxis) {
            $this->context
                ->buildViolation(RecordShouldNotBeUsedAsProductVariantAxis::ERROR_MESSAGE)
                ->addViolation();
        }
    }
}
