'use strict';
/**
 * Title extension for jobs
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/form/common/label', 'oro/translator'],
    function (BaseLabel, __) {
        return BaseLabel.extend({

            /**
             * Provide the object label
             *
             * @return {String}
             */
            getLabel: function () {
                var jobInstance = this.getFormData().jobInstance;
                var prefix = __('pim_import_export.form.job_execution.title.details');

                return prefix + ' - ' + jobInstance.label + ' [' + jobInstance.code + ']';
            }
        });
    }
);
