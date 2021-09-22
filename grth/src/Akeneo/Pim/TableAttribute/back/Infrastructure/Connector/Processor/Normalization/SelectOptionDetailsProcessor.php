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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Normalization;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Webmozart\Assert\Assert;

final class SelectOptionDetailsProcessor implements ItemProcessorInterface
{
    public function process($item): array
    {
        /** @var SelectOptionDetails $item */
        Assert::isInstanceOf($item, SelectOptionDetails::class);

        return [
            'attribute' => $item->attributeCode(),
            'column' => $item->columnCode(),
            'code' => $item->optionCode(),
            'labels' => $item->labels(),
        ];
    }
}
