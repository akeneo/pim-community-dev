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

class PriceCollectionValue implements SourceValueInterface
{
    /** @var Price[] */
    private array $data;

    public function __construct(array $data)
    {
        Assert::allIsInstanceOf($data, Price::class);

        $this->data = $data;
    }

    /** @return Price[] */
    public function getData(): array
    {
        return $this->data;
    }
}
