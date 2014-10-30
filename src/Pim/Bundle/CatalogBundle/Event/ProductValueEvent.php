<?php

namespace Pim\Bundle\CatalogBundle\Event;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Filter event allows to know the create product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueEvent extends Event
{
    /**
     * @var ProductManager
     */
    protected $manager;

    /**
     * @var ProductValueInterface
     */
    protected $value;

    /**
     * Constructor
     *
     * @param ProductManager        $manager the manager
     * @param ProductValueInterface $value   the product value
     */
    public function __construct(ProductManager $manager, ProductValueInterface $value)
    {
        $this->manager = $manager;
        $this->value   = $value;
    }

    /**
     * @return ProductManager
     */
    public function getProductManager()
    {
        return $this->manager;
    }

    /**
     * @return ProductValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }
}
