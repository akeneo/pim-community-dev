<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle;

/**
 * Security voter attributes
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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

    /** @staticvar string */
    const OWN_AT_LEAST_ONE_CATEGORY = 'OWN_AT_LEAST_ONE_CATEGORY';
}
