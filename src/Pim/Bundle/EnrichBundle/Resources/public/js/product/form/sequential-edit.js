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
        'pim/router',
        'pim/product-manager',
        'pim/fetcher-registry',
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
        router,
        ProductManager,
        FetcherRegistry,
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
                FetcherRegistry.clear('sequentialEdit');

                return $.when(
                    FetcherRegistry.getFetcher('sequential-edit')
                        .fetchAll()
                        .then(
                            function (sequentialEdit) {
                                this.model.set(sequentialEdit);
                            }.bind(this)
                        ),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            addSaveButton: function () {
                var objectSet    = this.model.get('objectSet');
                var currentIndex = objectSet.indexOf(this.getFormData().meta.id);
                var nextObject   = objectSet[currentIndex + 1];

                this.trigger('save-buttons:register-button', {
                    className: 'save-and-continue',
                    priority: 250,
                    label: _.__(
                        'pim_enrich.form.product.sequential_edit.btn.save_and_' + (nextObject ? 'next' : 'finish')
                    ),
                    events: {
                        'click .save-and-continue': this.saveAndContinue.bind(this)
                    }
                });
            },
            render: function () {
                if (!this.configured || !this.model.get('objectSet')) {
                    return this;
                }

                this.addSaveButton();

                this.getTemplateParameters().done(function (templateParameters) {
                    this.$el.html(this.template(templateParameters));
                    this.$('[data-toggle="tooltip"]').tooltip();
                    this.delegateEvents();
                    this.preloadNext();
                }.bind(this));

                return this;
            },
            getTemplateParameters: function () {
                var objectSet     = this.model.get('objectSet');
                var currentObject = this.getFormData().meta.id;
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

                return $.when.apply($, promises).then(function () {
                    return {
                        objectCount:    objectSet.length,
                        currentIndex:   index + 1,
                        previousObject: previousObject,
                        nextObject:     nextObject,
                        ratio:          (index + 1) / objectSet.length * 100
                    };
                });
            },
            preloadNext: function () {
                var objectSet = this.model.get('objectSet');
                var currentIndex = objectSet.indexOf(this.getFormData().meta.id);
                var pending = objectSet[currentIndex + 2];
                if (pending) {
                    setTimeout(function () {
                        ProductManager.get(pending);
                    }, 2000);
                }
            },
            saveAndContinue: function () {
                this.parent.getExtension('save').save({ silent: true }).done(function () {
                    var objectSet = this.model.get('objectSet');
                    var currentIndex = objectSet.indexOf(this.getFormData().meta.id);
                    var nextObject = objectSet[currentIndex + 1];
                    if (nextObject) {
                        this.goToProduct(nextObject);
                    } else {
                        this.finish();
                    }
                }.bind(this));
            },
            followLink: function (event) {
                this.getRoot().trigger('pim_enrich:form:state:confirm', {
                    action: function () {
                        this.goToProduct(event.currentTarget.dataset.id);
                    }.bind(this)
                });
            },
            goToProduct: function (id) {
                router.redirectToRoute('pim_enrich_product_edit', { id: id });
            },
            finish: function () {
                router.redirectToRoute('pim_enrich_product_index');
            }
        });
    }
);
