<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\Collection;

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
     * @return int
     */
    public function getId();

    /**
     * Getter required count
     *
     * @return int
     */
    public function getRequiredCount();

    /**
     * Getter ratio
     *
     * @return int
     */
    public function getRatio();

    /**
     * Getter locale
     *
     * @return LocaleInterface
     */
    public function getLocale();

    /**
     * Getter channel
     *
     * @return ChannelInterface
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
     * Get the missing attributes
     *
     * @return Collection
     */
    public function getMissingAttributes();
}
