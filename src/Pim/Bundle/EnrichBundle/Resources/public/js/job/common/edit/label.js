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
                // The key is for example 'pim_import_export.entity.import_profile.uppercase_label'
                const prefix = __('pim_import_export.entity.' + this.getFormData().type + '_profile.uppercase_label');

                return prefix + ' - ' + this.getFormData().label;
            }
        });
    }
);
