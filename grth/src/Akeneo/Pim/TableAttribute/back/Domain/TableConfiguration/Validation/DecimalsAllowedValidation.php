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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation;

use Webmozart\Assert\Assert;

final class DecimalsAllowedValidation implements TableValidation
{
    public const KEY = 'decimals_allowed';

    private bool $decimalsAllowed;

    private function __construct(bool $decimalsAllowed)
    {
        $this->decimalsAllowed = $decimalsAllowed;
    }

    public static function fromValue($decimalsAllowed): TableValidation
    {
        Assert::boolean($decimalsAllowed);

        return new self($decimalsAllowed);
    }

    public function getValue(): bool
    {
        return $this->decimalsAllowed;
    }
}
