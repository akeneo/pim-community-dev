'use strict';
/**
 * Display formatted user login details
 *
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/form/user/login-details'
    ],
    function (_, __, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                var user = this.getFormData();
                var createdDate = new Date(user.meta.created * 1000);
                var updatedDate = new Date(user.meta.updated * 1000);
                var lastLoginDate = new Date(user.last_login * 1000);
                this.$el.html(this.template({
                    __,
                    created: createdDate.toLocaleString(),
                    updated: updatedDate.toLocaleString(),
                    lastLoggedIn: lastLoginDate.toLocaleString(),
                    loginCount: user.login_count
                }));

                return BaseForm.prototype.render.apply(this, arguments);
            }
        });
    }
);
