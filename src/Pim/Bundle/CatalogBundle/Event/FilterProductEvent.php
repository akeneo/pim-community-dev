<?php

namespace Pim\Bundle\CatalogBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Filter event allows to know the create product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterProductEvent extends Event
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
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }
}
