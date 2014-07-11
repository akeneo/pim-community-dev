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
    const OWNER = 'OWNER';

    /** @staticvar string */
    const VIEW_PRODUCT = 'VIEW_PRODUCT';

    /** @staticvar string */
    const EDIT_PRODUCT = 'EDIT_PRODUCT';

    /** @staticvar string */
    const VIEW_ATTRIBUTES = 'GROUP_VIEW_ATTRIBUTES';

    /** @staticvar string */
    const EDIT_ATTRIBUTES = 'GROUP_EDIT_ATTRIBUTES';

    /** @staticvar string */
    const VIEW_PRODUCTS = 'CATEGORY_VIEW_PRODUCTS';

    /** @staticvar string */
    const EDIT_PRODUCTS = 'CATEGORY_EDIT_PRODUCTS';

    /** @staticvar string */
    const OWN_PRODUCTS = 'CATEGORY_OWN_PRODUCTS';

    /** @staticvar string */
    const EXECUTE_JOB_PROFILE = 'EXECUTE_JOB_PROFILE';

    /** @staticvar string */
    const EDIT_JOB_PROFILE    = 'EDIT_JOB_PROFILE';
}
