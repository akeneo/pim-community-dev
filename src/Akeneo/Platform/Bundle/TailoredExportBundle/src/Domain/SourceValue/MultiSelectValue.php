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

namespace Akeneo\Platform\TailoredExport\Domain\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Webmozart\Assert\Assert;

class MultiSelectValue implements SourceValueInterface
{
    /** @var string[] */
    private array $optionCodes;

    public function __construct(array $optionCodes)
    {
        Assert::allString($optionCodes);

        $this->optionCodes = $optionCodes;
    }

    public function getOptionCodes(): array
    {
        return $this->optionCodes;
    }
}
