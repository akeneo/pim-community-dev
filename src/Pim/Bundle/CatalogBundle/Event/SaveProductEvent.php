<?php

namespace Pim\Bundle\CatalogBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Event raised just before and after a product saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveProductEvent extends Event
{
    /** @var ProductManager */
    protected $manager;

    /** @var ProductInterface */
    protected $product;

    /** @var array */
    protected $options;

    /**
     * Constructor
     *
     * @param ProductManager   $manager the manager
     * @param ProductInterface $product the product
     * @param array            $options the saving options
     */
    public function __construct(ProductManager $manager, ProductInterface $product, $options)
    {
        $this->manager = $manager;
        $this->product = $product;
        $this->options = $options;
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

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
