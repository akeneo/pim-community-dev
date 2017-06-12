'use strict';

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
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/router',
        'pim/user-context',
        'pim/notifications',
        'pim/template/menu/user-navigation'
    ],
    function (
        _,
        __,
        BaseForm,
        router,
        UserContext,
        Notifications,
        template
    ) {
        return BaseForm.extend({
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
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
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
                }));

                var notificationView = new Notifications({
                    imgUrl: 'bundles/pimimportexport/images/loading.gif',
                    loadingText: __('Loading ...'),
                    noNotificationsMessage: __('pim_notification.no_notifications'),
                    markAsReadMessage: __('pim_notification.mark_all_as_read')
                });
                notificationView.setElement(this.$('.notification')).render();
                notificationView.refresh();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Redirect user to logout
             */
            logout: function () {
                router.redirectToRoute('oro_user_security_logout');
            },

            /**
             * Redirect user it's account details
             */
            userAccount: function () {
                router.redirectToRoute('oro_user_profile_view');
            }
        });
    });
