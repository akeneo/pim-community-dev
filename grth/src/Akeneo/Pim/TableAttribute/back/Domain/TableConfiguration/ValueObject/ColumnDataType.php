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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class ColumnDataType
{
    private string $dataType;

    private function __construct(string $dataType)
    {
        $this->dataType = $dataType;
    }

    public static function fromString(string $dataType): self
    {
        Assert::stringNotEmpty($dataType);

        return new self($dataType);
    }

    public function asString(): string
    {
        return $this->dataType;
    }
}
