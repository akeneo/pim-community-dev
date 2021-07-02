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

class CategoriesValue implements SourceValueInterface
{
    private array $categoryCodes;

    public function __construct(array $categoryCodes)
    {
        Assert::allString($categoryCodes);

        $this->categoryCodes = $categoryCodes;
    }

    public function getCategoryCodes(): array
    {
        return $this->categoryCodes;
    }
}