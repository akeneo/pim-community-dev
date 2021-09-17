<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query\FindRecordsUsedAsProductVariantAxisInterface;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class RecordsShouldNotBeUsedAsProductVariantAxisValidator extends ConstraintValidator
{
    private FindRecordsUsedAsProductVariantAxisInterface $findRecordsUsedAsProductVariantAxis;

    public function __construct(
        FindRecordsUsedAsProductVariantAxisInterface $findRecordsUsedAsProductVariantAxis
    ) {
        $this->findRecordsUsedAsProductVariantAxis = $findRecordsUsedAsProductVariantAxis;
    }

    public function validate($command, Constraint $constraint): void
    {
        if (!$constraint instanceof RecordsShouldNotBeUsedAsProductVariantAxis) {
            throw new UnexpectedTypeException($constraint, RecordsShouldNotBeUsedAsProductVariantAxis::class);
        }

        if (!$command instanceof DeleteRecordsCommand) {
            throw new UnexpectedTypeException($command, DeleteRecordsCommand::class);
        }

        $recordsAreUsedAsProductVariantAxis = $this->findRecordsUsedAsProductVariantAxis->areUsed(
            $command->recordCodes,
            $command->referenceEntityIdentifier
        );

        if ($recordsAreUsedAsProductVariantAxis) {
            $this->context
                ->buildViolation(RecordShouldNotBeUsedAsProductVariantAxis::ERROR_MESSAGE)
                ->addViolation();
        }
    }
}
