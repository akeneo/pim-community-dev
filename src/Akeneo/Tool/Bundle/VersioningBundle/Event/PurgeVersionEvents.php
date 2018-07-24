<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Event;

/**
 * Purge version events
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PurgeVersionEvents
{
    /**
     * This event is dispatched before the version is being processed by the advisors
     *
     * The event listener receives PurgeVersionEvent instance
     */
    const PRE_ADVISEMENT = 'pim_versioning.purge_version.pre_advisement';

    /**
     * This event is dispatched before a version is purged
     *
     * Thee event listener receives PurgeVersionEvent instance
     */
    const PRE_PURGE = 'pim_versioning.purge_version.pre_purge';
}
