<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemoveProductModelCommand
{
    private string $productModelCode;

    public function __construct(string $productModelCode)
    {
        $this->productModelCode = $productModelCode;
    }

    public function productModelCode(): string
    {
        return $this->productModelCode;
    }
}
