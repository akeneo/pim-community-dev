<?php

namespace Pim\Bundle\CatalogBundle\Event;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Filter event allows to know the create product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEvent extends Event
{
    /**
     * @var ProductManager
     */
    protected $manager;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * Constructor
     *
     * @param ProductManager   $manager the manager
     * @param ProductInterface $product the product
     */
    public function __construct(ProductManager $manager, ProductInterface $product)
    {
        $this->manager = $manager;
        $this->product = $product;
    }

    /**
     * @return ProductManager
     */
    public function getProductManager()
    {
        return $this->manager;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }
}
