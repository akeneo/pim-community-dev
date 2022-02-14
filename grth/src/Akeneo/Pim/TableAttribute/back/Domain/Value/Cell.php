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

namespace Akeneo\Pim\TableAttribute\Domain\Value;

use Webmozart\Assert\Assert;

final class Cell
{
    /** @var scalar|array<string, string> */
    private $data;

    /**
     * @param scalar|array<string, string> $data
     */
    private function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @param scalar|array<string, string> $data
     */
    public static function fromNormalized($data): self
    {
        /* @phpstan-ignore-next-line */
        if (!is_scalar($data) && !is_array($data)) {
            throw new \InvalidArgumentException('The cell value must be a scalar or an array');
        }
        Assert::notSame($data, '');
        Assert::notSame($data, []);

        return new self($data);
    }

    /**
     * @return scalar|array<string, string>
     */
    public function normalize()
    {
        return $this->data;
    }
}
