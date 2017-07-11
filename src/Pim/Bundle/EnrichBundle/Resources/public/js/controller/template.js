

import $ from 'jquery'
import _ from 'underscore'
import BaseController from 'pim/controller/base'
export default BaseController.extend({
            /**
             * {@inheritdoc}
             */
    renderRoute: function (route, path) {
        return $.get(path)
                    .then(this.renderTemplate.bind(this))
                    .promise()
    },

            /**
             * Add the given content to the current container
             *
             * @param {String} content
             */
    renderTemplate: function (content) {
        if (!this.active) {
            return
        }

        this.$el.html(content)
    }
})

