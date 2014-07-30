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
    const VIEW = 'VIEW';

    /** @staticvar string */
    const EDIT = 'EDIT';

    /** @staticvar string */
    const EXECUTE = 'EXECUTE';

    /** @staticvar string */
    const OWN = 'OWN';

    /** @staticvar string */
    const VIEW_ATTRIBUTES = 'GROUP_VIEW_ATTRIBUTES';

    /** @staticvar string */
    const EDIT_ATTRIBUTES = 'GROUP_EDIT_ATTRIBUTES';

    /** @staticvar string */
    const VIEW_PRODUCTS = 'VIEW_PRODUCTS';

    /** @staticvar string */
    const EDIT_PRODUCTS = 'EDIT_PRODUCTS';

    /** @staticvar string */
    const OWN_PRODUCTS = 'OWN_PRODUCTS';
}
