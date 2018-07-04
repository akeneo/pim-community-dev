<?php

namespace Pim\Component\Catalog\Completeness;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

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
     * Generate completeness for a product.
     *
     * @param ProductInterface $product
     */
    public function generateMissingForProduct(ProductInterface $product);

    /**
     * Generate completeness for products given a channel and filters
     *
     * @param ChannelInterface $channel
     * @param array $filters
     */
    public function generateMissingForProducts(ChannelInterface $channel, array $filters);

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
}
