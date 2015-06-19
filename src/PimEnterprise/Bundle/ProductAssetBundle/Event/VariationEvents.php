<?php

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

/**
 * Catalog association type events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class VariationEvents
{
    /**
     * This event is thrown before an association type is removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const UPLOAD_PRE = 'pimee_product_asset.variation.upload.pre';
    const UPLOAD_POST = 'pimee_product_asset.variation.upload.post';
}
