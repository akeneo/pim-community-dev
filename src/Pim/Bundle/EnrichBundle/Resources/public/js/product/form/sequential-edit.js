'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'text!pim/template/product/sequential-edit',
        'routing',
        'oro/navigation',
        'pim/product-manager',
        'bootstrap'
    ],
    function (
        $,
        _,
        Backbone,
        mediator,
        BaseForm,
        template,
        Routing,
        Navigation,
        ProductManager
    ) {
        var SequentialEdit = Backbone.Model.extend({
            url: Routing.generate('pim_enrich_mass_edit_action_sequential_edit_get')
        });

        return BaseForm.extend({
            id: 'sequentialEdit',
            template: _.template(template),
            events: {
                'click .next, .previous': 'followLink'
            },
            initialize: function () {
                this.model = new SequentialEdit();
            },
            configure: function () {
                var deferred = $.Deferred();

                this.model.fetch({
                    success: deferred.resolve
                });

                return $.when(
                    deferred.promise(),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            render: function () {
                if (!this.configured || !this.model.get('objectSet')) {
                    return this;
                }

                this.getTemplateParameters().done(_.bind(function (templateParameters) {
                    this.$el.html(this.template(templateParameters));
                    this.$('[data-toggle="tooltip"]').tooltip();
                    this.delegateEvents();
                }, this));

                return this;
            },
            getTemplateParameters: function () {
                var deferred = $.Deferred();

                var objectSet = this.model.get('objectSet');
                var currentObject = this.getData().meta.id;
                var index = objectSet.indexOf(currentObject);
                var previous = objectSet[index - 1];
                var next = objectSet[index + 1];

                var previousObject = null;
                var nextObject = null;

                var promises = [];
                if (previous) {
                    promises.push(ProductManager.get(previous).then(function (product) {
                        var label = product.meta.label;
                        previousObject = {
                            id:         product.meta.id,
                            label:      label,
                            shortLabel: label.length > 25 ? label.slice(0, 22) + '...' : label
                        };
                    }));
                }
                if (next) {
                    promises.push(ProductManager.get(next).then(function (product) {
                        var label = product.meta.label;
                        nextObject = {
                            id:         product.meta.id,
                            label:      label,
                            shortLabel: label.length > 25 ? label.slice(0, 22) + '...' : label
                        };
                    }));
                }

                $.when.apply($, promises).done(function () {
                    deferred.resolve(
                        {
                            objectCount:    objectSet.length,
                            currentIndex:   index + 1,
                            previousObject: previousObject,
                            nextObject:     nextObject,
                            ratio:          (index + 1) / objectSet.length * 100
                        }
                    );
                });

                return deferred.promise();
            },
            followLink: function (event) {
                Navigation.getInstance().setLocation(
                    Routing.generate(
                        'pim_enrich_product_edit',
                        { id: event.currentTarget.dataset.id }
                    )
                );
            }
        });
    }
);
