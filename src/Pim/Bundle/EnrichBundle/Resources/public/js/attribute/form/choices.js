/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/template/common/form-container'
],
function (
    _,
    __,
    BaseForm,
    template
) {
    return BaseForm.extend({
        className: 'tab-content',
        template: _.template(template),
        config: {},

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
        configure: function () {
            if (_.contains(this.config.activeForTypes, this.getRoot().getType())) {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __(this.config.label)
                });
            }

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            if (!_.contains(this.config.activeForTypes, this.getRoot().getType())) {
                return;
            }

            this.$el.html(this.template());

            this.renderExtensions();
        }
    });
});
