'use strict';

/**
 * Delete extension for job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/job-instance-import'], function (DeleteForm, JobInstanceRemover) {
    return DeleteForm.extend({
        remover: JobInstanceRemover
    });
});
