'use strict';
/**
 * Edit form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alps <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'backbone',
        'pim/template/common/default-template',
        'pim/form',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/field-manager',
        'pim/form-builder',
        'oro/messenger'
    ],
    function (
        _,
        __,
        Backbone,
        template,
        BaseForm,
        mediator,
        FetcherRegistry,
        FieldManager,
        formBuilder,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                mediator.clear('pim_enrich:form');
                Backbone.Router.prototype.once('route', this.unbindEvents);

                if (_.has(__moduleConfig, 'forwarded-events')) {
                    this.forwardMediatorEvents(__moduleConfig['forwarded-events']);
                }

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.displayError.bind(this));

                this.onExtensions('save-buttons:register-button', function (button) {
                    this.getExtension('save-buttons').trigger('save-buttons:add-button', button);
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.getRoot().trigger('pim_enrich:form:render:before');

                this.$el.html(this.template());

                this.renderExtensions();

                this.getRoot().trigger('pim_enrich:form:render:after');
            },

            /**
             * Clear the mediator
             */
            unbindEvents: function () {
                mediator.clear('pim_enrich:form');
            },

            /**
             * Clear the cached information
             */
            clearCache: function () {
                FetcherRegistry.clearAll();
                FieldManager.clearFields();
                this.render();
            },

            /**
             * Display validation error as flash message
             *
             * @param {Event} event
             */
            displayError: function (event) {
                _.each(event.response, function (error) {
                    if (error.global) {
                        messenger.notify('error', error.message);
                    }
                })
            },

            /**
             * Get header size of the form
             *
             * @return {number}
             */
            headerSize: function () {
                return this.el.querySelector('header.navigation').offsetHeight;
            }
        });
    }
);
