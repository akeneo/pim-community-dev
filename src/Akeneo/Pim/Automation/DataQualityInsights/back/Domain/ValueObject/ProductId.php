<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class ProductId
{
    /** @var int */
    private $id;

    public function __construct(int $id)
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('Product id should be a positive integer');
        }

        $this->id = $id;
    }

    public function toInt(): int
    {
        return $this->id;
    }

    public function __toString()
    {
        return strval($this->id);
    }
}
