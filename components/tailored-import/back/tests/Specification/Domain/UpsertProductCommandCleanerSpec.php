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

namespace Specification\Akeneo\Platform\TailoredImport\Domain;

use Akeneo\Pim\Enrichment\Product\API\Command\Exception\ViolationsException;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class UpsertProductCommandCleanerSpec extends ObjectBehavior
{
    public function it_removes_only_invalid_user_intents()
    {
        $notAValueUserIntentConstraintViolation = new ConstraintViolation(
            'Not a value user intent violation',
            null,
            [],
            '',
            'name',
            ''
        );
        $descriptionConstraintViolation = new ConstraintViolation(
            'error',
            null,
            [],
            '',
            'valueUserIntents[1]',
            'A description with error'
        );
        $constraintViolationList = new ConstraintViolationList([$descriptionConstraintViolation]);
        $violationsException = new ViolationsException($constraintViolationList);

        $upsertProductCommand = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'identifier',
            valueUserIntents: [
                new SetTextValue('name', null, null, value: 'A name'),
                new SetTextValue('description', null, null, 'A description with error'),
            ]
        );

        $expectedUpsertProductCommand = new UpsertProductCommand(
            userId: 1,
            productIdentifier: 'identifier',
            valueUserIntents: [
                new SetTextValue('name', null, null, value: 'A name'),
            ]
        );

        $this::removeInvalidUserIntents($violationsException, $upsertProductCommand)->shouldBeLike($expectedUpsertProductCommand);
    }
}
