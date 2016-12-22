'use strict';
/**
 * Label extension for jobs
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/form/common/label'],
    function (BaseLabel) {
        return BaseLabel.extend({

            /**
             * Provide the object label
             *
             * @return {String}
             */
            getLabel: function () {
                return this.getFormData().label;
            }
        });
    }
)
