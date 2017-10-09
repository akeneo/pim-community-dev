"use strict";

define([
        'oro/translator',
        'backbone',
        'oro/mediator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/template/common/default-template'
    ],
    function(
        __,
        Backbone,
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

                if (_.has(__moduleConfig, 'forwarded-events')) {
                    this.forwardMediatorEvents(__moduleConfig['forwarded-events']);
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

                this.$el.html(this.template());

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
