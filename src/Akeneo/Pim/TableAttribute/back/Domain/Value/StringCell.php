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

final class StringCell implements CellInterface
{
    private string $data;

    private function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * @param mixed $data
     */
    public static function fromNormalized($data): self
    {
        Assert::stringNotEmpty($data);

        return new self($data);
    }

    public function normalize(): string
    {
        return $this->data;
    }
}
