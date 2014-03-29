<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

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
     * Generate completeness for a product
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
     * Return products count per channel
     * It returns the same set of products to export, but doesn't consider the completeness ratio,
     * and group them by channel
     * Example:
     *    array(
     *        array(
     *            'label' => 'Mobile',
     *            'total' => 100,
     *        ),
     *        array(
     *            'label' => 'E-Commerce',
     *            'total' => 85,
     *        ),
     *    )
     *
     * @return array
     */
    public function getProductsCountPerChannels();

    /**
     * Return complete products count per channel and locales
     * It returns the same set of products to export and group them by channel and locale
     * Example:
     *    array(
     *        array(
     *            'label' => 'Mobile',
     *            'locale' => 'en_US',
     *            'total' => 10,
     *        ),
     *        array(
     *            'label' => 'E-Commerce',
     *            'locale' => 'en_US',
     *            'total' => 85,
     *        ),
     *        array(
     *            'label' => 'Mobile',
     *            'locale' => 'fr_FR',
     *            'total' => 5,
     *        ),
     *        array(
     *            'label' => 'E-Commerce',
     *            'locale' => 'fr_FR',
     *            'total' => 63,
     *        ),
     *    )
     *
     * @return array
     */
    public function getCompleteProductsCountPerChannels();
}
