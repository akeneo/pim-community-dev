

/**
 * Text view extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import BaseField from 'pim/job/common/edit/field/field'
import fieldTemplate from 'pim/template/export/common/edit/field/text'
export default BaseField.extend({
    fieldTemplate: _.template(fieldTemplate),
    events: {
        'change input': 'updateState'
    },

        /**
         * Get the field dom value
         *
         * @return {string}
         */
    getFieldValue: function () {
        return this.$('input').val()
    }
})

