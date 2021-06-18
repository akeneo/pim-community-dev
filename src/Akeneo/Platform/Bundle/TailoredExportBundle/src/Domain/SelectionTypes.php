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

namespace Akeneo\Platform\TailoredExport\Domain;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 */
final class SelectionTypes
{
    public const AMOUNT = 'amount';
    public const CODE = 'code';
    public const CURRENCY = 'currency';
    public const LABEL = 'label';
    public const UNIT = 'unit';
}
