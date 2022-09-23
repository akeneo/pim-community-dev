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

namespace Akeneo\Platform\Syndication\Application\Common\Selection\PriceCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Webmozart\Assert\Assert;

final class PriceSelection implements SelectionInterface
{
    public const TYPE = 'price';

    private string $currency;

    public function __construct(string $currency)
    {
        Assert::string($currency);

        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
