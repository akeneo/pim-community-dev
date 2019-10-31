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
        'pim/media-url-generator',
        'pim/template/menu/user-navigation'
    ],
    function (
        _,
        __,
        BaseForm,
        router,
        UserContext,
        Notifications,
        MediaUrlGenerator,
        template
    ) {
        return BaseForm.extend({
            className: 'AknTitleContainer-userMenu',
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
                    firstName: UserContext.get('first_name'),
                    lastName: UserContext.get('last_name'),
                    avatar: this.getAvatar(),
                    logoutLabel: __(this.config.logout),
                    userAccountLabel: __(this.config.userAccount)
                }));

                var notificationView = new Notifications({
                    imgUrl: 'bundles/pimimportexport/images/loading.gif',
                    loadingText: __('pim_common.loading'),
                    noNotificationsMessage: __('pim_notification.no_notifications'),
                    markAsReadMessage: __('pim_notification.mark_all_as_read')
                });
                notificationView.setElement(this.$('.notification')).render();
                notificationView.refresh();

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Redirect user to logout
             */
            logout: function () {
                window.location = router.generate('pim_user_logout_redirect');
            },

            /**
             * Redirect user it's account details
             */
            userAccount: function () {
                router.redirectToRoute(
                    'pim_user_edit',
                    {identifier: UserContext.get('meta').id}
                );
            },

            /**
             * Return user's avatar
             */
            getAvatar: function () {
                const filePath = UserContext.get('avatar').filePath;
                if (null === filePath || undefined === filePath) {
                    return null;
                }

                return MediaUrlGenerator.getMediaShowUrl(UserContext.get('avatar').filePath, 'thumbnail_small');
            }
        });
    });
