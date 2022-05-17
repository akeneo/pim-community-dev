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

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
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

    public function getCellData(string $columnUuid): ValueInterface
    {
        Assert::keyExists($this->cells, $columnUuid);

        if (0 === strlen($this->cells[$columnUuid])) {
            return new NullValue();
        }

        return new StringValue($this->cells[$columnUuid]);
    }
}
