<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * CompletenessRepository interface
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CompletenessRepositoryInterface
{
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
