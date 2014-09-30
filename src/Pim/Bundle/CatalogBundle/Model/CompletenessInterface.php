<?php
namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Product completeness interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CompletenessInterface
{
    /**
     * Getter required count
     *
     * @return int
     */
    public function getRequiredCount();

    /**
     * Setter locale
     *
     * @param Locale $locale
     *
     * @return CompletenessInterface
     */
    public function setLocale(Locale $locale);

    /**
     * Getter ratio
     *
     * @return int
     */
    public function getRatio();

    /**
     * Setter missing count
     *
     * @param integer $missingCount
     *
     * @return CompletenessInterface
     */
    public function setMissingCount($missingCount);

    /**
     * Setter channel
     *
     * @param Channel $channel
     *
     * @return CompletenessInterface
     */
    public function setChannel(Channel $channel);

    /**
     * Setter product
     *
     * @param ProductInterface $product
     *
     * @return CompletenessInterface
     */
    public function setProduct(ProductInterface $product);

    /**
     * Getter locale
     *
     * @return Locale
     */
    public function getLocale();

    /**
     * Getter channel
     *
     * @return Channel
     */
    public function getChannel();

    /**
     * Getter missing count
     *
     * @return int
     */
    public function getMissingCount();

    /**
     * Getter product
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Setter required count
     *
     * @param int $requiredCount
     *
     * @return CompletenessInterface
     */
    public function setRequiredCount($requiredCount);

    /**
     * Setter ratio
     *
     * @param int $ratio
     *
     * @return CompletenessInterface
     */
    public function setRatio($ratio);
}
