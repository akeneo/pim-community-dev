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
    const VIEW = 'VIEW_RESOURCE';

    const EDIT = 'EDIT_RESOURCE';

    const EXECUTE = 'EXECUTE_RESOURCE';

    const OWN = 'OWN_RESOURCE';

    const VIEW_ATTRIBUTES = 'VIEW_ATTRIBUTES';

    const EDIT_ATTRIBUTES = 'EDIT_ATTRIBUTES';

    const VIEW_ITEMS = 'VIEW_ITEMS';

    const EDIT_ITEMS = 'EDIT_ITEMS';

    const OWN_PRODUCTS = 'OWN_PRODUCTS';

    const OWN_AT_LEAST_ONE_CATEGORY = 'OWN_AT_LEAST_ONE_CATEGORY';
}
