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
    /** @var scalar */
    private $data;

    /**
     * @param scalar $data
     */
    private function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @param scalar $data
     */
    public static function fromNormalized($data): self
    {
        Assert::scalar($data);
        Assert::notSame($data, '');

        return new self($data);
    }

    /**
     * @return scalar
     */
    public function normalize()
    {
        return $this->data;
    }
}
