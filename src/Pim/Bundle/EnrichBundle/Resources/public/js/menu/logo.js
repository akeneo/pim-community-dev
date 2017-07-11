
/**
 * Base extension forheadermenu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import BaseForm from 'pim/form'
import router from 'pim/router'
import template from 'pim/template/menu/logo'
export default BaseForm.extend({
  className: 'AknHeader-menuItem',
  template: _.template(template),
  events: {
    'click': 'backHome'
  },

            /**
             * {@inheritdoc}
             */
  render: function () {
    this.$el.html(this.template())

    return BaseForm.prototype.render.apply(this, arguments)
  },

            /**
             * Redirect the user to app's home
             */
  backHome: function () {
    router.redirectToRoute('oro_default')
  }
})
