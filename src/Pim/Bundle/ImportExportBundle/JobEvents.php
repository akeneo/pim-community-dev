<?php

namespace Pim\Bundle\ImportExportBundle;

/**
 * Job events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class JobEvents
{
    /**
     * This event is thrown before a job profile is edited
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_EDIT_JOB_PROFILE       = 'pim_import_export.job_profile.pre_edit';

    /**
     * This event is thrown after a job profile has been edited
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_EDIT_JOB_PROFILE = 'pim_import_export.job_profile.post_edit';

    /**
     * This event is thrown before a job profile is executed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_EXECUTE_JOB_PROFILE = 'pim_import_export.job_profile.pre_execute';

    /**
     * This event is thrown after a job profile has been executed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_EXECUTE_JOB_PROFILE = 'pim_import_export.job_profile.post_execute';

    /**
     * This event is thrown before a job profile is removed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE_JOB_PROFILE = 'pim_import_export.job_profile.pre_remove';

    /**
     * This event is thrown before a job profile is shown
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SHOW_JOB_PROFILE = 'pim_import_export.job_profile.pre_show';

    /**
     * This event is thrown each a job execution is being shown
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SHOW_JOB_EXECUTION = 'pim_import_export.job_execution.pre_show';

    /**
     * This event is thrown before the log of a job execution is downloaded
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_DOWNLOAD_LOG_JOB_EXECUTION = 'pim_import_export.job_execution.pre_dl_log';

    /**
     * This event is thrown before the files of a job execution is downloaded
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_DOWNLOAD_FILES_JOB_EXECUTION = 'pim_import_export.job_execution.pre_dl_files';
}
