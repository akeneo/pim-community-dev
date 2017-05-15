"use strict";

define([
        'backbone',
        'config',
        'oro/mediator',
        'pim/form',
        'pim/fetcher-registry',
        'oro/template/system/config'
    ],
    function(
        Backbone,
        module,
        mediator,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.model = new Backbone.Model({});

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                Backbone.Router.prototype.once('route', this.unbindEvents);

                if (_.has(module.config(__moduleName), 'forwarded-events')) {
                    this.forwardMediatorEvents(module.config(__moduleName)['forwarded-events']);
                }

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.getRoot().trigger('oro_config:form:render:before');

                this.$el.html(this.template({}));

                this.renderExtensions();

                this.getRoot().trigger('oro_config:form:render:after');

                return this;
            },

            /**
             * Clear the mediator events
             */
            unbindEvents: function () {
                mediator.clear('oro_config:form');
            }
        });
});
