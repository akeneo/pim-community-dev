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

namespace Akeneo\Platform\TailoredExport\Domain\Model\Selection\Date;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Date\DateFormat;

final class DateSelection implements DateSelectionInterface
{
    private string $format;

    public function __construct(string $format)
    {
        if (!DateFormat::isValidFormat($format)) {
            throw new \InvalidArgumentException(sprintf('Date format "%s" is not supported', $format));
        }

        $this->format = $format;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
