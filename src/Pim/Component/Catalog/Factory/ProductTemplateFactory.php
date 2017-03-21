<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;

/**
 * Creates and configures a product template instance.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateFactory implements SimpleFactoryInterface
{
    /** @var string */
    protected $productTemplateClass;

    /**
     * @param string $productTemplateClass
     */
    public function __construct($productTemplateClass)
    {
        $this->productTemplateClass = $productTemplateClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->productTemplateClass();
    }
}
