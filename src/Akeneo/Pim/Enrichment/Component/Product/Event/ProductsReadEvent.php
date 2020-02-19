<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Event;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ProductsReadEvent
{
    /** @var int[] */
    private $productIds;

    public function __construct(array $productIds)
    {
        $this->productIds = $productIds;
    }

    /**
     * @return int[]
     */
    public function productIds(): array
    {
        return $this->productIds;
    }
}
