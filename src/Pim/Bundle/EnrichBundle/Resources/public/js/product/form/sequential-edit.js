'use strict';
/**
 * Sequential edit extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        'pim/entity-manager',
        'pim/user-context',
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
        ProductManager,
        EntityManager,
        UserContext
    ) {
        return BaseForm.extend({
            id: 'sequentialEdit',
            template: _.template(template),
            events: {
                'click .next, .previous': 'followLink'
            },
            initialize: function () {
                this.model = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                mediator.once('hash_navigation_request:start', function (navigation) {
                    if (navigation.url === Routing.generate('pim_enrich_product_index')) {
                        EntityManager.clear('sequentialEdit');
                    }
                });

                return $.when(
                    EntityManager.getRepository('sequentialEdit').findAll().then(
                        _.bind(function (sequentialEdit) {
                            this.model.set(sequentialEdit);
                        }, this)
                    ),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addSaveButton: function () {
                if (!('save-buttons' in this.parent.extensions)) {
                    return;
                }
                var objectSet = this.model.get('objectSet');
                var currentIndex = objectSet.indexOf(this.getData().meta.id);
                var nextObject = objectSet[currentIndex + 1];

                this.parent.extensions['save-buttons'].addButton({
                    className: 'save-and-continue',
                    priority: 250,
                    label: _.__(
                        'pim_enrich.form.product.sequential_edit.btn.save_and_' + (nextObject ? 'next' : 'finish')
                    ),
                    events: {
                        'click .save-and-continue': _.bind(this.saveAndContinue, this)
                    }
                });
            },
            render: function () {
                if (!this.configured || !this.model.get('objectSet')) {
                    return this;
                }

                this.addSaveButton();

                this.getTemplateParameters().done(_.bind(function (templateParameters) {
                    this.$el.html(this.template(templateParameters));
                    this.$('[data-toggle="tooltip"]').tooltip();
                    this.delegateEvents();
                    this.preloadNext();
                }, this));

                return this;
            },
            getTemplateParameters: function () {
                var deferred = $.Deferred();

                var objectSet     = this.model.get('objectSet');
                var currentObject = this.getData().meta.id;
                var index         = objectSet.indexOf(currentObject);
                var previous      = objectSet[index - 1];
                var next          = objectSet[index + 1];

                var previousObject = null;
                var nextObject = null;

                var promises = [];
                if (previous) {
                    promises.push(ProductManager.get(previous).then(function (product) {
                        var label = product.meta.label[UserContext.get('catalogLocale')];
                        previousObject = {
                            id:         product.meta.id,
                            label:      label,
                            shortLabel: label.length > 25 ? label.slice(0, 22) + '...' : label
                        };
                    }));
                }
                if (next) {
                    promises.push(ProductManager.get(next).then(function (product) {
                        var label = product.meta.label[UserContext.get('catalogLocale')];
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
            preloadNext: function () {
                var objectSet = this.model.get('objectSet');
                var currentIndex = objectSet.indexOf(this.getData().meta.id);
                var pending = objectSet[currentIndex + 2];
                if (pending) {
                    setTimeout(function () {
                        ProductManager.clear(pending);
                        ProductManager.get(pending);
                    }, 2000);
                }
            },
            saveAndContinue: function () {
                this.parent.extensions.save.save({ silent: true }).done(_.bind(function () {
                    var objectSet = this.model.get('objectSet');
                    var currentIndex = objectSet.indexOf(this.getData().meta.id);
                    var nextObject = objectSet[currentIndex + 1];
                    if (nextObject) {
                        this.goToProduct(nextObject);
                    } else {
                        this.finish();
                    }
                }, this));
            },
            followLink: function (event) {
                this.goToProduct(event.currentTarget.dataset.id);
            },
            goToProduct: function (id) {
                Navigation.getInstance().setLocation(
                    Routing.generate(
                        'pim_enrich_product_edit',
                        { id: id }
                    )
                );
            },
            finish: function () {
                Navigation.getInstance().setLocation(Routing.generate('pim_enrich_product_index'));
            }
        });
    }
);
