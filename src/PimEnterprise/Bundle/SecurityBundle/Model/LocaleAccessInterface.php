<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * LocaleInterface access interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface LocaleAccessInterface extends AccessInterface
{
    /**
     * @param LocaleInterface $locale
     *
     * @return LocaleAccessInterface
     */
    public function setLocale(LocaleInterface $locale);

    /**
     * @return Locale
     */
    public function getLocale();

    /**
     * @param bool $editProducts
     *
     * @return LocaleAccessInterface
     */
    public function setEditProducts($editProducts);

    /**
     * @return bool
     */
    public function isEditProducts();

    /**
     * @param bool $viewProducts
     *
     * @return LocaleAccessInterface
     */
    public function setViewProducts($viewProducts);

    /**
     * @return bool
     */
    public function isViewProducts();
}
