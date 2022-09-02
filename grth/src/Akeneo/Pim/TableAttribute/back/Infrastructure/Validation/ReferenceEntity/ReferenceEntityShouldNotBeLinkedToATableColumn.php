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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ReferenceEntity;

use Symfony\Component\Validator\Constraint;

final class ReferenceEntityShouldNotBeLinkedToATableColumn extends Constraint
{
    public string $message = 'pim_table_configuration.validation.reference_entity.should_not_be_linked';

    /**
     * {@inheritDoc}
     */
    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
