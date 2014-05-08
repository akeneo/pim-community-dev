<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;

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
     * Generate completeness for a product. CAUTION: in current
     * implementations, the product must already be flushed to the DBs
     *
     * @param ProductInterface $product
     */
    public function generateMissingForProduct(ProductInterface $product);

    /**
     * Generate completeness for a channel
     *
     * @param Channel $channel
     */
    public function generateMissingForChannel(Channel $channel);

    /**
     * Generate missing completenesses
     */
    public function generateMissing();

    /**
     * Schedule recalculation of completenesses for a product
     *
     * @param ProductInterface $product
     */
    public function schedule(ProductInterface $product);

    /**
     * Schedule recalculation of completenesses for all product
     * of a family
     *
     * @param Family $family
     */
    public function scheduleForFamily(Family $family);

    /**
     * Schedule recalculation of completenesses for all products
     * of a channel and a locale id
     *
     * @param Channel $channel
     * @param Locale  $locale
     */
    public function scheduleForChannelAndLocale(Channel $channel, Locale $locale);
}
