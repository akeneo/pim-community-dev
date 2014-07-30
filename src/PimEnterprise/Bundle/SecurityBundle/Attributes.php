<?php

namespace PimEnterprise\Bundle\SecurityBundle;

/**
 * Security voter attributes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
final class Attributes
{
    /** @staticvar string */
    const VIEW = 'VIEW_RESOURCE';

    /** @staticvar string */
    const EDIT = 'EDIT_RESOURCE';

    /** @staticvar string */
    const EXECUTE = 'EXECUTE_RESOURCE';

    /** @staticvar string */
    const OWN = 'OWN_RESOURCE';

    /** @staticvar string */
    const VIEW_ATTRIBUTES = 'VIEW_ATTRIBUTES';

    /** @staticvar string */
    const EDIT_ATTRIBUTES = 'EDIT_ATTRIBUTES';

    /** @staticvar string */
    const VIEW_PRODUCTS = 'VIEW_PRODUCTS';

    /** @staticvar string */
    const EDIT_PRODUCTS = 'EDIT_PRODUCTS';

    /** @staticvar string */
    const OWN_PRODUCTS = 'OWN_PRODUCTS';
}
