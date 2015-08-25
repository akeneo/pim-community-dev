<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

/**
 * Interface ProductManagerInterface
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductManagerInterface
{
    /**
     * @return ProductRepositoryInterface
     */
    public function getProductRepository();

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getAttributeRepository();
}
