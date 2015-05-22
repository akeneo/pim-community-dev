 'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'routing',
        'pim/form',
        'pim/field-manager',
        'pim/entity-manager',
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
        EntityManager,
        AttributeManager,
        ProductManager,
        AttributeGroupManager,
        UserContext,
        SecurityContext,
        formTemplate,
        Dialog,
        messenger
    ) {
        var FormView = BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable tabs-left product-attributes',
            events: {
                'click .remove-attribute': 'removeAttribute'
            },
            visibleFields: {},
            rendering: false,
            initialize: function () {
                FieldManager.fields = {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('attributes', 'Attributes');

                this.listenTo(this.getRoot().model, 'change', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                mediator.on('product:action:post_update', _.bind(this.postSave, this));
                mediator.on('product:action:post_validation_error', _.bind(this.postValidationError, this));
                mediator.on('show_attribute', _.bind(this.showAttribute, this));
                window.addEventListener('resize', _.bind(this.resize, this));

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            render: function () {
                if (!this.configured || this.rendering) {
                    return this;
                }

                this.rendering = true;

                this.getConfig().done(_.bind(function () {
                    this.$el.html(this.template({}));
                    this.resize();
                    var product = this.getData();
                    $.when(
                        EntityManager.getRepository('family').findAll(),
                        ProductManager.getValues(product)
                    ).done(_.bind(function (families, values) {
                        var productValues = AttributeGroupManager.getAttributeGroupValues(
                            values,
                            this.extensions['attribute-group-selector'].getCurrentAttributeGroup()
                        );

                        var fieldPromisses = [];
                        _.each(productValues, _.bind(function (productValue, attributeCode) {
                            fieldPromisses.push(this.renderField(product, attributeCode, productValue, families));
                        }, this));

                        $.when.apply($, fieldPromisses).done(_.bind(function () {
                            var $productValuesPanel = this.$('.product-values');
                            $productValuesPanel.empty();

                            this.visibleFields = {};
                            _.each(arguments, _.bind(function (field) {
                                field.render();
                                this.visibleFields[field.attribute.code] = field;
                                $productValuesPanel.append(field.$el);
                            }, this));
                            this.rendering = false;
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
                        optional: AttributeManager.isOptional(attributeCode, product, families),
                        removable: SecurityContext.isGranted('pim_enrich_product_remove_attribute')
                    });
                    field.setValues(values);

                    return field;
                });
            },
            getConfig: function () {
                var promises = [];
                var product = this.getData();

                promises.push(this.extensions['attribute-group-selector'].updateAttributeGroups(product));
                if (this.extensions['add-attribute']) {
                    promises.push(this.extensions['add-attribute'].updateOptionalAttributes(product));
                }

                return $.when.apply($, promises).promise();
            },
            addAttributes: function (attributeCodes) {
                EntityManager.getRepository('attribute').findAll().done(_.bind(function (attributes) {
                    var product = this.getData();

                    var hasRequiredValues = true;
                    _.each(attributeCodes, function (attributeCode) {
                        var attribute = _.findWhere(attributes, {code: attributeCode});
                        if (!product.values[attribute.code]) {
                            product.values[attribute.code] = [AttributeManager.getValue(
                                [],
                                attribute,
                                UserContext.get('catalogLocale'),
                                UserContext.get('catalogScope')
                            )];
                            hasRequiredValues = false;
                        }
                    });

                    this.extensions['attribute-group-selector'].setCurrent(
                        _.findWhere(attributes, {code: _.first(attributeCodes)}).group
                    );

                    if (hasRequiredValues) {
                        this.getRoot().model.trigger('change');
                        return;
                    }

                    /* jshint sub:true */
                    /* jscs:disable requireDotNotation */
                    this.extensions['copy'].generateCopyFields();

                    this.setData(product);
                    this.getRoot().model.trigger('change');
                }, this));

            },
            removeAttribute: function (event) {
                if (!SecurityContext.isGranted('pim_enrich_product_remove_attribute')) {
                    return;
                }
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getData();
                var fields = FieldManager.getFields();

                Dialog.confirm(
                    _.__('pim_enrich.confirmation.delete.product_attribute'),
                    _.__('pim_enrich.confirmation.delete_item'),
                    _.bind(function () {
                        EntityManager.getRepository('attribute').find(attributeCode).done(_.bind(function (attribute) {
                            $.ajax({
                                type: 'DELETE',
                                url: Routing.generate(
                                    'pim_enrich_product_remove_attribute_rest',
                                    {
                                        productId: this.getData().meta.id,
                                        attributeId: attribute.id
                                    }
                                ),
                                contentType: 'application/json'
                            }).then(_.bind(function () {
                                if (this.extensions['add-attribute']) {
                                    this.extensions['add-attribute'].updateOptionalAttributes(product);
                                }

                                delete product.values[attributeCode];
                                delete fields[attributeCode];
                                /* jshint sub:true */
                                this.extensions['copy'].generateCopyFields();
                                /* jscs:enable requireDotNotation */

                                this.setData(product);

                                this.getRoot().model.trigger('change');
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
            postValidationError: function () {
                this.render();
                this.extensions['attribute-group-selector'].removeBadges();
                _.each(FieldManager.getFields(), function (field) {
                    if (!field.getValid()) {
                        mediator.trigger('show_attribute', {attribute: field.attribute.code});
                        return;
                    }
                });
                this.updateAttributeGroupBadges();
            },
            postSave: function () {
                FieldManager.fields = {};
                this.extensions['attribute-group-selector'].removeBadges();

                this.render();
            },
            updateAttributeGroupBadges: function () {
                var fields = FieldManager.getFields();

                AttributeGroupManager.getAttributeGroupsForProduct(this.getData())
                    .done(_.bind(function (attributeGroups) {
                        _.each(fields, _.bind(function (field) {
                            var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                                attributeGroups,
                                field.attribute.code
                            );

                            if (!field.getValid()) {
                                this.extensions['attribute-group-selector'].addToBadge(attributeGroup, 'invalid');
                            }
                        }, this));
                    }, this));
            },
            showAttribute: function (event) {
                AttributeGroupManager.getAttributeGroupsForProduct(this.getData())
                    .done(_.bind(function (attributeGroups) {
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
            }
        });

        return FormView;
    }
);
