<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily\Event;

use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is raise when a product is turned into a variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentWasAddedToProduct extends Event
{
    /** @var VariantProductInterface */
    private $variantProduct;

    /** @var string */
    private $parentCode;

    public const EVENT_NAME = 'ADD_PARENT_A_PRODUCT';

    /**
     * @param VariantProductInterface    $variantProduct
     * @param string $parentCode
     */
    public function __construct(VariantProductInterface $variantProduct, string $parentCode)
    {
        $this->variantProduct = $variantProduct;
        $this->parentCode = $parentCode;
    }

    /**
     * @return VariantProductInterface
     */
    public function convertedProduct(): VariantProductInterface
    {
        return $this->variantProduct;
    }

    /**
     * @return string
     */
    public function parentCode(): string
    {
        return $this->parentCode;
    }
}
