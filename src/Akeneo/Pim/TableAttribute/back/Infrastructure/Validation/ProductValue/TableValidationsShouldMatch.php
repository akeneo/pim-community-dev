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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Symfony\Component\Validator\Constraint;

final class TableValidationsShouldMatch extends Constraint
{
    public const MIN_MESSAGE = 'This value should be {{ limit }} or more.';
    public const MAX_MESSAGE = 'This value should be {{ limit }} or less.';
    public const DECIMALS_ALLOWED_MESSAGE = 'The required value is an integer';
    public const MAX_LENGTH_MESSAGE = 'This value should contain {{ limit }} characters or less.';

    /**
     * {@inheritDoc}
     */
    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
