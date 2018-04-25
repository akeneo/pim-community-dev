<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Context;

use Context\EnterpriseNavigationContext;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class NavigationContext extends EnterpriseNavigationContext
{
    /** @var array */
    protected $pageDecorators = [
        'PimEnterprise\Behat\Decorator\Page\GridCapableDecorator',
    ];
}
