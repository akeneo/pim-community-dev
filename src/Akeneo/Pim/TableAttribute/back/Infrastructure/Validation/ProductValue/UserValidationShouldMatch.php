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

final class UserValidationShouldMatch extends Constraint
{
    public const MIN_MESSAGE = 'TODO This value should be {{ limit }} or more.';
    public const MAX_MESSAGE = 'TODO This value should be {{ limit }} or less.';
    public const DECIMALS_ALLOWED_MESSAGE = 'TODO This value should not allow decimal.';
    public const MAX_LENGTH_MESSAGE = 'TODO This value should contain {{ limit }} characters or less.';

    public function getTargets(): array
    {
        return [Constraint::CLASS_CONSTRAINT];
    }
}
