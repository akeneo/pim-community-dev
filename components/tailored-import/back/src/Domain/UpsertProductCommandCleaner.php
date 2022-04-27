<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain;

use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Symfony\Component\Validator\ConstraintViolationInterface;

class UpsertProductCommandCleaner
{
    public static function removeInvalidUserIntents(
        ViolationsException $violationsException,
        UpsertProductCommand $upsertProductCommand,
    ): UpsertProductCommand {
        $valueUserIntents = $upsertProductCommand->valueUserIntents();

        /** @var ConstraintViolationInterface $violation */
        foreach ($violationsException->violations() as $violation) {
            if (str_starts_with($violation->getPropertyPath(), 'valueUserIntents[')
                && str_ends_with($violation->getPropertyPath(), ']')
            ) {
                $propertyPath = substr($violation->getPropertyPath(), strlen('valueUserIntents') + 1, -1);

                unset($valueUserIntents[$propertyPath]);
            }
        }

        return new UpsertProductCommand(
            userId: $upsertProductCommand->userId(),
            productIdentifier: $upsertProductCommand->productIdentifier(),
            valueUserIntents: $valueUserIntents,
        );
    }
}
