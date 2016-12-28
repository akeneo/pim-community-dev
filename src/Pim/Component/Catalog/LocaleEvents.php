<?php

namespace Pim\Component\Catalog;

/**
 * Locale events.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class LocaleEvents
{
    /**
     * This event is dispatched each time a locale is deactivated.
     *
     * The event listener receives an
     * Pim\Bundle\EnrichBundle\Event\BuildProductValueFormEvent instance.
     *
     * @staticvar string
     */
    const LOCALE_DEACTIVATED = 'pim_enrich.locale_deactivated';

    /**
     * This event is dispatched each time a locale is activated.
     *
     * The event listener receives an
     * Pim\Bundle\EnrichBundle\Event\BuildProductValueFormEvent instance.
     *
     * @staticvar string
     */
    const LOCALE_ACTIVATED = 'pim_enrich.locale_activated';
}
