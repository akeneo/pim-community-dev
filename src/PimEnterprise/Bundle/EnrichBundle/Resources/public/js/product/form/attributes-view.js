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
        'routing',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/product-manager',
        'pim/attribute-group-manager',
        'pim/user-context',
        'pim/security-context',
        'text!pim/template/product/tab/attributes'
    ],
    function (
        $,
        _,
        Backbone,
        Routing,
        BaseForm,
        FieldManager,
        FetcherRegistry,
        AttributeManager,
        ProductManager,
        AttributeGroupManager,
        UserContext,
        SecurityContext,
        formTemplate
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable tabs-left product-attributes',
            rendering: false,
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.product.tab.attributes.title')
                });

                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:show_attribute', this.showAttribute);

                window.addEventListener('resize', this.resize.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:render:after', this.resize);
                FieldManager.clearFields();

                this.onExtensions('attribute-group:change', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured || this.rendering) {
                    return this;
                }

                this.rendering = true;
                this.getConfig().done(function () {
                    this.$el.html(this.template({}));
                    this.resize();
                    var product = this.getFormData();
                    ProductManager.getValues(product).then(function (values) {
                        var productValues = AttributeGroupManager.getAttributeGroupValues(
                            values,
                            this.getExtension('attribute-group-selector').getCurrentAttributeGroup()
                        );

                        var fieldPromises = [];
                        _.each(productValues, function (productValue, attributeCode) {
                            fieldPromises.push(this.renderField(product, attributeCode, productValue));
                        }.bind(this));

                        this.rendering = false;

                        return $.when.apply($, fieldPromises);
                    }.bind(this)).then(function () {
                        var $productValuesPanel = this.$('.product-values');
                        $productValuesPanel.empty();

                        FieldManager.clearVisibleFields();
                        _.each(arguments, function (field) {
                            if (field.canBeSeen()) {
                                field.render();
                                FieldManager.addVisibleField(field.attribute.code);
                                $productValuesPanel.append(field.$el);
                            }
                        }.bind(this));
                    }.bind(this));
                    this.delegateEvents();

                    this.renderExtensions();
                }.bind(this));

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
            renderField: function (product, attributeCode, values) {
                return FieldManager.getField(attributeCode).then(function (field) {
                    field.setContext({
                        locale: UserContext.get('catalogLocale'),
                        scope: UserContext.get('catalogScope'),
                        uiLocale: UserContext.get('catalogLocale'),
                        optional: false,
                        removable: false
                    });
                    field.setValues(values);

                    return field;
                });
            },
            getConfig: function () {
                var promises = [];
                var product = this.getFormData();

                promises.push(this.getExtension('attribute-group-selector').updateAttributeGroups(product));

                return $.when.apply($, promises).promise();
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
                    .done(function (attributeGroups) {
                        this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.code);

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

                        var attributeGroupSelector = this.getExtension('attribute-group-selector');
                        if (attributeGroup !== attributeGroupSelector.getCurrent()) {
                            attributeGroupSelector.setCurrent(attributeGroup);
                            needRendering = true;
                        }

                        if (needRendering) {
                            this.render();
                        }

                        FieldManager.getFields()[event.attribute].setFocus();
                    }.bind(this));
            }
        });
    }
);
