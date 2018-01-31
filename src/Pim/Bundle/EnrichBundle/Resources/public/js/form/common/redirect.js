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
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'routing',
        'pim/router',
        'pim/common/property',
        'pim/template/form/redirect'
    ],
    function ($, _, __, BaseForm, Routing, router, propertyAccessor, template) {
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
                this.isVisible().then(function (isVisible) {
                    if (!isVisible) {
                        return this;
                    }

                    this.$el.html(this.template({
                        label: __(this.config.label),
                        buttonClass: this.config.buttonClass || 'AknButton--action'
                    }));
                }.bind(this));

                return this;
            },

            /**
             * Redirect to the route given in the config
             */
            redirect: function () {
                router.redirect(this.getUrl());
            },

            /**
             * Get the route to redirect to
             *
             * @return {string}
             */
            getUrl: function () {
                var params = {};
                if (this.config.identifier) {
                    params[this.config.identifier.name] = propertyAccessor.accessProperty(
                        this.getFormData(),
                        this.config.identifier.path
                    );
                }

                return Routing.generate(this.config.route, params);
            },

            /**
             * Should this extension render
             *
             * @return {Promise}
             */
            isVisible: function () {
                return $.Deferred().resolve(true).promise();
            }
        });
    }
);
