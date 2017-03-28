<?php

namespace Pim\Component\Catalog\Completeness;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Completeness generator interface.
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
     * @param ChannelInterface $channel
     */
    public function generateMissingForChannel(ChannelInterface $channel);

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
     * @param FamilyInterface $family
     */
    public function scheduleForFamily(FamilyInterface $family);

    /**
     * Schedule recalculation of completenesses for all products
     * of a channel and a locale id
     *
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     */
    public function scheduleForChannelAndLocale(ChannelInterface $channel, LocaleInterface $locale);
}
