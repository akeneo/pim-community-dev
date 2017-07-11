
import $ from 'jquery'
import _ from 'underscore'
import BaseController from 'pim/controller/base'
import router from 'pim/router'
export default BaseController.extend({
            /**
             * {@inheritdoc}
             */
  renderRoute: function (route, path) {
    return $.get(path).then(this.redirect.bind(this)).promise()
  },

            /**
             * Redirect to the given route
             *
             * @param {Object} response
             */
  redirect: function (response) {
    if (!this.active) {
      return
    }

    router.redirectToRoute(
                    response.route,
                    response.params ? response.params : {},
                    {trigger: true}
                )
  }
})
