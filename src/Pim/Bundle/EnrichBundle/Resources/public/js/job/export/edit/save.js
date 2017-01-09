'use strict';

/**
 * Save extension for job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/job-instance-edit-form/save',
        'pim/saver/job-instance-export'
    ],
    function (
        BaseSave,
        JobInstanceSaver
    ) {
        return BaseSave.extend({
            /**
             * {@inheritdoc}
             */
            getJobInstanceSaver: function () {
                return JobInstanceSaver;
            }
        });
    }
);
