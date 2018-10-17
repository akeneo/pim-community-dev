<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Event;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class JobInstanceEvents
{
    /**
     * This event is dispatched after a job instance is created or updated by the UI.
     */
    const POST_SAVE = 'pim_enrich.job_instance.post_save';
}
