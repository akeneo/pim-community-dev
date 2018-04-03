<?php

namespace Pim\Component\Catalog\Completeness;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Completeness remover interface.
 *
 * It's not a {@link Akeneo\Component\StorageUtils\Saver\SaverInterface}
 * as its purpose is not to remove the completenesses objects that are given as argument, but
 * instead to remove the completenesses that are linked to a given product, family or couple
 * locale/channel.
 *
 * @author    Julien Janvier (j.janvier@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface CompletenessRemoverInterface
{
    /**
     * Remove completenesses of a product
     *
     * @param ProductInterface $product
     */
    public function removeForProduct(ProductInterface $product);

    /**
     * Remove completenesses for all product of a family
     *
     * @param FamilyInterface $family
     */
    public function removeForFamily(FamilyInterface $family);

    /**
     * Remove completenesses for all products of a channel and a locale
     *
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     */
    public function removeForChannelAndLocale(ChannelInterface $channel, LocaleInterface $locale);
}
