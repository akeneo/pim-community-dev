<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is raised when a product is converted to a variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentHasBeenAddedToProduct extends Event
{
    private ProductInterface $variantProduct;
    private string $parentCode;

    public const EVENT_NAME = 'PARENT_HAS_BEEN_ADDED_TO_PRODUCT';

    public function __construct(ProductInterface $variantProduct, string $parentCode)
    {
        $this->variantProduct = $variantProduct;
        $this->parentCode = $parentCode;
    }

    public function convertedProduct(): ProductInterface
    {
        return $this->variantProduct;
    }

    public function parentCode(): string
    {
        return $this->parentCode;
    }
}
