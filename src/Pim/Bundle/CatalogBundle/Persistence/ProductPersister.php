<?php

namespace Pim\Bundle\CatalogBundle\Persistence;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;

/**
 * Synchronize product with the database
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductPersister
{
    /**
     * Save a product in the database
     *
     * @param ProductInterface $product
     */
    public function persist(ProductInterface $product, array $options);
}
