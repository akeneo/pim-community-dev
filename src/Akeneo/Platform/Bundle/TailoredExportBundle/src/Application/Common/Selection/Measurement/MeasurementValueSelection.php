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

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\Measurement;

final class MeasurementValueSelection implements MeasurementSelectionInterface
{
    public const TYPE = 'value';

    private string $decimalSeparator;

    public function __construct(string $decimalSeparator)
    {
        $this->decimalSeparator = $decimalSeparator;
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }
}
