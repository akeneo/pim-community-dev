<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Event;

/**
 * Job profile events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class JobProfileEvents
{
    /**
     * This event is thrown before a job profile is edited
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_EDIT = 'pim_import_export.job_profile.pre_edit';

    /**
     * This event is thrown after a job profile has been edited
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_EDIT = 'pim_import_export.job_profile.post_edit';

    /**
     * This event is thrown before a job profile is executed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_EXECUTE = 'pim_import_export.job_profile.pre_execute';

    /**
     * This event is thrown after a job profile has been executed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_EXECUTE = 'pim_import_export.job_profile.post_execute';

    /**
     * This event is thrown before a job profile is removed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_import_export.job_profile.pre_remove';

    /**
     * This event is thrown before a job profile is shown
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SHOW = 'pim_import_export.job_profile.pre_show';
}
