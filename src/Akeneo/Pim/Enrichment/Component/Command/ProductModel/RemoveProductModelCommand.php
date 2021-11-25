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

namespace Akeneo\Pim\Enrichment\Component\Command\ProductModel;

final class RemoveProductModelCommand
{
    private int $productModelId;

    public function __construct(int $productModelId)
    {
        $this->productModelId = $productModelId;
    }

    public function productModelId(): int
    {
        return $this->productModelId;
    }
}
