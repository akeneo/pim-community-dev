'use strict';
/**
 * Title extension for jobs
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
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
                var prefix = __('pim_enrich.form.job_instance.title.' + this.getFormData().type);

                return prefix + ' - ' + this.getFormData().label;
            }
        });
    }
);
