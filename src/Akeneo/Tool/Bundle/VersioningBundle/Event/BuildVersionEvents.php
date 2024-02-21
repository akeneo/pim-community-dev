<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Event;

/**
 * Build version events
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BuildVersionEvents
{
    /**
     * This event is dispatched before build a new version
     *
     * The event listener receives a
     * Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent instance
     *
     * @staticvar string
     */
    const PRE_BUILD = 'pim_versioning.build_version.pre_build';
}
