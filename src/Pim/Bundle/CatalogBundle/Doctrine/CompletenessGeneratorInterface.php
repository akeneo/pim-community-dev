<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Completeness generator interface. Will be implemented differently
 * depending of the Product storage
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CompletenessGeneratorInterface
{
    /**
     * Generate missing completeness according to the criteria
     * The limit is used to do batch generation to avoid
     * locking the Completeness during a too long time
     *
     * @param array   $criteria
     * @param integer $limit
     */
    public function generate(array $criteria = array(), $limit = null);

    /**
     * Schedule recalculation of completenesses for a product
     *
     * @param ProductInterface $product
     */
    public function schedule(ProductInterface $product);
}
