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
        'module',
        'underscore',
        'oro/translator',
        'backbone',
        'text!pim/template/form/edit-form',
        'pim/form',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/field-manager'
    ],
    function (
        module,
        _,
        __,
        Backbone,
        template,
        BaseForm,
        mediator,
        FetcherRegistry,
        FieldManager
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                mediator.clear('pim_enrich:form');
                Backbone.Router.prototype.once('route', this.unbindEvents);

                if (_.has(module.config(), 'forwarded-events')) {
                    this.forwardMediatorEvents(module.config()['forwarded-events']);
                }

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
             * Clear the cached informations
             */
            clearCache: function () {
                FetcherRegistry.clearAll();
                FieldManager.clearFields();
                this.render();
            }
        });
    }
);
