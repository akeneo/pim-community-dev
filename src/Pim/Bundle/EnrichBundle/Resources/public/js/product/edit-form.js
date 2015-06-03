'use strict';

define(
    [
        'underscore',
        'backbone',
        'text!pim/template/product/form',
        'pim/form',
        'oro/mediator',
        'pim/entity-manager',
        'pim/field-manager'
    ],
    function (
        _,
        Backbone,
        template,
        BaseForm,
        mediator,
        EntityManager,
        FieldManager
    ) {
        var FormView = BaseForm.extend({
            template: _.template(template),
            initialize: function () {
                this.model = new Backbone.Model();

                mediator.off(null, null, 'context:product:form:init');
                mediator.on('entity:error:save', _.bind(this.clearCache, this), 'context:product:form:init');

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({
                        product: this.getData()
                    })
                );

                return this.renderExtensions();
            },
            clearCache: function () {
                EntityManager.clearAll();
                FieldManager.clear();
                this.render();
            }
        });

        return FormView;
    }
);
