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

namespace Akeneo\Platform\TailoredExport\Domain\Model\SourceValue;

class SimpleSelectValue implements SourceValueInterface
{
    private string $optionCode;

    public function __construct(string $optionCode)
    {
        $this->optionCode = $optionCode;
    }

    public function getOptionCode(): string
    {
        return $this->optionCode;
    }
}
