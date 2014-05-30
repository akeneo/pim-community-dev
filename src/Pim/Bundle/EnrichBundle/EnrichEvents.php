<?php

namespace Pim\Bundle\EnrichBundle;

/**
 * Enrich events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class EnrichEvents
{
    /**
     * This event is thrown each time a product value form is created in the system.
     *
     * The event listener receives an
     * Pim\Bundle\EnrichBundle\Event\BuildProductValueFormEvent instance.
     *
     * @staticvar string
     */
    const CREATE_PRODUCT_VALUE_FORM = 'pim_enrich.build_product_value_form';
}
