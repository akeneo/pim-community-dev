
/**
 * This extension will display the user navigation.
 * The user navigation contains:
 * - The link to display the user options
 * - The notification menu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import router from 'pim/router'
import UserContext from 'pim/user-context'
import Notifications from 'pim/notifications'
import template from 'pim/template/menu/user-navigation'
export default BaseForm.extend({
  className: 'AknHeader-userMenu',
  template: _.template(template),
  events: {
    'click .logout': 'logout',
    'click .user-account': 'userAccount'
  },

            /**
             * {@inheritdoc}
             */
  initialize: function (config) {
    this.config = config.config

    BaseForm.prototype.initialize.apply(this, arguments)
  },

            /**
             * {@inheritdoc}
             */
  render: function () {
    this.$el.html(this.template({
      firstName: UserContext.get('firstName'),
      lastName: UserContext.get('lastName'),
      avatar: UserContext.get('avatar'),
      logoutLabel: __(this.config.logout),
      userAccountLabel: __(this.config.userAccount)
    }))

    var notificationView = new Notifications({
      imgUrl: 'bundles/pimimportexport/images/loading.gif',
      loadingText: __('pim_notification.loading'),
      noNotificationsMessage: __('pim_notification.no_notifications'),
      markAsReadMessage: __('pim_notification.mark_all_as_read')
    })
    notificationView.setElement(this.$('.notification')).render()
    notificationView.refresh()

    return BaseForm.prototype.render.apply(this, arguments)
  },

            /**
             * Redirect user to logout
             */
  logout: function () {
    router.redirectToRoute('oro_user_security_logout')
  },

            /**
             * Redirect user it's account details
             */
  userAccount: function () {
    router.redirectToRoute('oro_user_profile_view')
  }
})
