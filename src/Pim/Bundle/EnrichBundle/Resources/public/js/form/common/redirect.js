'use strict';
/**
 * Redirect button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'routing',
        'oro/navigation',
        'pim/common/property',
        'text!pim/template/form/redirect'
    ],
    function (_, __, BaseForm, Routing, Navigation, propertyAccessor, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click': 'redirect'
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
                    __: __,
                    label: this.config.label
                }));

                return this;
            },

            /**
             * Redirect to the route given in the config
             */
            redirect: function () {
                 Navigation.getInstance().setLocation(this.getUrl());
            },

            /**
             * Get the route to redirect to
             *
             * @return {string}
             */
            getUrl: function () {
                var params = {};
                params[this.config.identifier.name] = propertyAccessor.accessProperty(
                    this.getFormData(),
                    this.config.identifier.path
                );

                return Routing.generate(this.config.route, params);
            }
        });
    }
);
