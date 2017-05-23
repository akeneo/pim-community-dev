/**
 * Basic view that simply renders a template.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'oro/translator',
    'pim/form'
], function (
    $,
    _,
    Backbone,
    __,
    BaseForm
) {
    return BaseForm.extend({
        config: {},

        /**
         * {@inheritdoc}
         */
        initialize: function (meta) {
            this.config = meta.config;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            if (undefined === this.config.template) {
                throw new Error('The view "' + this.code + '" must be configured with a template.');
            }

            require(['text!' + this.config.template], function (template) {
                var templateParams = this.config.templateParams || {};
                templateParams = _.extend({}, templateParams, {__: __});

                this.$el.html(
                    _.template(template)(templateParams)
                );

                this.renderExtensions();
            }.bind(this));
        }
    });
});
