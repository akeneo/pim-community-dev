<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Application\Common;

use Webmozart\Assert\Assert;

class Row
{
    public function __construct(private array $cells)
    {
        foreach ($cells as $key => $cell) {
            Assert::uuid($key);
            Assert::string($cell);
        }
    }

    public function getCellData(string $columnUuid): string
    {
        Assert::keyExists($this->cells, $columnUuid);

        return $this->cells[$columnUuid];
    }
}
