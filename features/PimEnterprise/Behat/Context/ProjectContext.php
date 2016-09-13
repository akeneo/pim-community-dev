<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Behat\Context;

use Akeneo\ActivityManager\Behat\Context;
use Akeneo\ActivityManager\Behat\ContextInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProjectContext extends Context implements ContextInterface
{
    /**
     * @Then /^I should be on the project show page/
     */
    public function iShouldBeOnTheProjectPage()
    {
        Assert::true($this->getCurrentPage()->isOpen());
    }
}
