<?php

namespace Specification\Akeneo\Platform\Bundle\PimVersionBundle\Version;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommunityVersionSpec extends ObjectBehavior
{
    /**
     * Do not change it during a pull up.
     * It is useful to hardcode it as master, as it allows to follow who installed EE master thanks to the analytics.
     */
    function it_checks_version()
    {
        $this->versionCodename()->shouldReturn('Sahara Hare');
        $this->editionName()->shouldReturn('CE');
    }
}
