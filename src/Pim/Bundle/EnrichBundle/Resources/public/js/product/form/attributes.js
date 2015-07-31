 'use strict';
/**
 * Attribute tab extension
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
        'routing',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/product-manager',
        'pim/attribute-group-manager',
        'pim/user-context',
        'pim/security-context',
        'text!pim/template/product/tab/attributes',
        'pim/dialog',
        'oro/messenger'
    ],
    function (
        $,
        _,
        Backbone,
        mediator,
        Routing,
        BaseForm,
        FieldManager,
        FetcherRegistry,
        AttributeManager,
        ProductManager,
        AttributeGroupManager,
        UserContext,
        SecurityContext,
        formTemplate,
        Dialog,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable tabs-left product-attributes',
            events: {
                'click .remove-attribute': 'removeAttribute'
            },
            rendering: false,
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.product.tab.attributes.title')
                });

                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                this.listenTo(mediator, 'pim_enrich:form:entity:validation_error', this.render);
                this.listenTo(mediator, 'pim_enrich:form:change-family:after', this.render);
                this.listenTo(mediator, 'pim_enrich:form:add-attribute:after', this.render);
                this.listenTo(mediator, 'pim_enrich:form:entity:post_update', this.postSave);
                this.listenTo(mediator, 'pim_enrich:form:show_attribute', this.showAttribute);

                window.addEventListener('resize', _.bind(this.resize, this));
                this.listenTo(mediator, 'pim_enrich:form:render:after', this.resize);
                FieldManager.clearFields();

                this.onExtensions('comparison:change', _.bind(this.comparisonChange, this));
                this.onExtensions('attribute-group:change', _.bind(this.render, this));
                this.onExtensions('add-attribute:add', _.bind(this.addAttributes, this));
                this.onExtensions('copy:copy-fields:after', _.bind(this.render, this));
                this.onExtensions('copy:select:after', _.bind(this.render, this));
                this.onExtensions('copy:context:change', _.bind(this.render, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured || this.rendering) {
                    return this;
                }

                this.rendering = true;
                this.getConfig().done(_.bind(function () {
                    this.$el.html(this.template({}));
                    this.resize();
                    var product = this.getFormData();
                    $.when(
                        FetcherRegistry.getFetcher('family').fetchAll(),
                        ProductManager.getValues(product)
                    ).then(_.bind(function (families, values) {
                        var productValues = AttributeGroupManager.getAttributeGroupValues(
                            values,
                            this.extensions['attribute-group-selector'].getCurrentAttributeGroup()
                        );

                        var fieldPromises = [];
                        _.each(productValues, _.bind(function (productValue, attributeCode) {
                            fieldPromises.push(this.renderField(product, attributeCode, productValue, families));
                        }, this));

                        this.rendering = false;

                        return $.when.apply($, fieldPromises);
                    }, this)).then(_.bind(function () {
                        var $productValuesPanel = this.$('.product-values');
                        $productValuesPanel.empty();

                        FieldManager.clearVisibleFields();
                        _.each(arguments, _.bind(function (field) {
                            if (field.canBeSeen()) {
                                field.render();
                                FieldManager.addVisibleField(field.attribute.code);
                                $productValuesPanel.append(field.$el);
                            }
                        }, this));
                    }, this));
                    this.delegateEvents();

                    this.renderExtensions();
                }, this));

                return this;
            },
            resize: function () {
                var productValuesContainer = this.$('.product-values');
                if (productValuesContainer.length && this.getRoot().$el.length && productValuesContainer.offset()) {
                    productValuesContainer.css(
                        {'height': ($(window).height() - productValuesContainer.offset().top - 4) + 'px'}
                    );
                }
            },
            renderField: function (product, attributeCode, values, families) {
                return FieldManager.getField(attributeCode).then(function (field) {
                    field.setContext({
                        locale: UserContext.get('catalogLocale'),
                        scope: UserContext.get('catalogScope'),
                        uiLocale: UserContext.get('catalogLocale'),
                        optional: AttributeManager.isOptional(field.attribute, product, families),
                        removable: SecurityContext.isGranted('pim_enrich_product_remove_attribute')
                    });
                    field.setValues(values);

                    return field;
                });
            },
            getConfig: function () {
                var promises = [];
                var product = this.getFormData();

                promises.push(this.extensions['attribute-group-selector'].updateAttributeGroups(product));

                return $.when.apply($, promises).promise();
            },
            addAttributes: function (event) {
                var attributeCodes = event.codes;

                $.when(
                    FetcherRegistry.getFetcher('attribute').fetchAll(),
                    FetcherRegistry.getFetcher('locale').fetchAll(),
                    FetcherRegistry.getFetcher('channel').fetchAll(),
                    FetcherRegistry.getFetcher('currency').fetchAll()
                ).then(_.bind(function (attributes, locales, channels, currencies) {
                    var product = this.getFormData();

                    _.each(attributeCodes, function (attributeCode) {
                        var attribute = _.findWhere(attributes, {code: attributeCode});
                        if (!product.values[attribute.code]) {
                            product.values[attribute.code] = AttributeManager.generateMissingValues(
                                [],
                                attribute,
                                locales,
                                channels,
                                currencies
                            );
                        }
                    });

                    this.extensions['attribute-group-selector'].setCurrent(
                        _.findWhere(attributes, {code: _.first(attributeCodes)}).group
                    );

                    this.setData(product);

                    mediator.trigger('pim_enrich:form:add-attribute:after');
                }, this));
            },
            removeAttribute: function (event) {
                if (!SecurityContext.isGranted('pim_enrich_product_remove_attribute')) {
                    return;
                }
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getFormData();
                var fields = FieldManager.getFields();

                Dialog.confirm(
                    _.__('pim_enrich.confirmation.delete.product_attribute'),
                    _.__('pim_enrich.confirmation.delete_item'),
                    _.bind(function () {
                        FetcherRegistry.getFetcher('attribute').fetch(attributeCode).done(_.bind(function (attribute) {
                            $.ajax({
                                type: 'DELETE',
                                url: Routing.generate(
                                    'pim_enrich_product_remove_attribute_rest',
                                    {
                                        productId: this.getFormData().meta.id,
                                        attributeId: attribute.id
                                    }
                                ),
                                contentType: 'application/json'
                            }).then(_.bind(function () {
                                this.triggerExtensions('add-attribute:update:available-attributes');

                                delete product.values[attributeCode];
                                delete fields[attributeCode];

                                this.setData(product);

                                mediator.trigger('pim_enrich:form:remove-attribute:after');
                            }, this)).fail(function () {
                                messenger.notificationFlashMessage(
                                    'error',
                                    _.__('pim_enrich.form.product.flash.attribute_deletion_error')
                                );
                            });
                        }, this));
                    }, this)
                );
            },
            setScope: function (scope, options) {
                UserContext.set('catalogScope', scope, options);
            },
            getScope: function () {
                return UserContext.get('catalogScope');
            },
            setLocale: function (locale, options) {
                UserContext.set('catalogLocale', locale, options);
            },
            getLocale: function () {
                return UserContext.get('catalogLocale');
            },
            postSave: function () {
                FieldManager.fields = {};
                this.render();
            },
            showAttribute: function (event) {
                AttributeGroupManager.getAttributeGroupsForProduct(this.getFormData())
                    .done(_.bind(function (attributeGroups) {
                        mediator.trigger('pim_enrich:form:form-tabs:change', this.code);

                        var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                            attributeGroups,
                            event.attribute
                        );
                        var needRendering = false;

                        if (!attributeGroup) {
                            return;
                        }

                        if (event.scope) {
                            this.setScope(event.scope, {silent: true});
                            needRendering = true;
                        }
                        if (event.locale) {
                            this.setLocale(event.locale, {silent: true});
                            needRendering = true;
                        }

                        if (attributeGroup !== this.extensions['attribute-group-selector'].getCurrent()) {
                            this.extensions['attribute-group-selector'].setCurrent(attributeGroup);
                            needRendering = true;
                        }

                        if (needRendering) {
                            this.render();
                        }

                        FieldManager.getFields()[event.attribute].setFocus();
                    }, this));
            },
            comparisonChange: function (open) {
                this.$el[open ? 'addClass' : 'removeClass']('comparison-mode');
            }
        });
    }
);
