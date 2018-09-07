<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Event;

/**
 * Job execution events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class JobExecutionEvents
{
    /**
     * This event is thrown each a job execution is being shown
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SHOW = 'pim_import_export.job_execution.pre_show';

    /**
     * This event is thrown before the log of a job execution is downloaded
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_DOWNLOAD_LOG = 'pim_import_export.job_execution.pre_dl_log';

    /**
     * This event is thrown before the files of a job execution is downloaded
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_DOWNLOAD_FILES = 'pim_import_export.job_execution.pre_dl_files';
}
