<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentHasBeenRemovedFromVariantProduct extends Event
{
    /** @var ProductInterface */
    private $product;

    /** @var string */
    private $formerParentProductModelCode;

    public function __construct(ProductInterface $product, string $formerParentProductModelCode)
    {
        $this->product = $product;
        $this->formerParentProductModelCode = $formerParentProductModelCode;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function getFormerParentProductModelCode(): string
    {
        return $this->formerParentProductModelCode;
    }
}
