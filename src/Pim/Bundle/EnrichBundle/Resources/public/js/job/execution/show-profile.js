
/**
 * Redirect button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import Redirect from 'pim/common/redirect'
import Routing from 'routing'
export default Redirect.extend({
            /**
             * {@inheritdoc}
             */
    initialize: function (config) {
        this.config = config.config

        Redirect.prototype.initialize.apply(this, arguments)
    },

            /**
             * Get the route to redirect to
             *
             * @return {string}
             */
    getUrl: function () {
        var code = this.getFormData().jobInstance.code
        var type = this.getFormData().jobInstance.type
        var route = 'pim_importexport_%type%_profile_show'.replace('%type%', type)

        return Routing.generate(route, {
            code: code
        })
    },

            /**
             * Only visible when the type of jobInstance is import or export
             *
             * @returns {*|{then, fail, end}}
             */
    isVisible: function () {
        var type = this.getFormData().jobInstance.type

        return $.Deferred().resolve(type === 'export' || type === 'import').promise()
    }

})

