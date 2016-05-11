'use strict';
/**
 * Add attribute extension
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
        'pim/form',
        'pim/attribute-manager',
        'text!pim/template/product/tab/attribute/add-attribute',
        'pim/attribute/add-attribute-line',
        'pim/attribute/add-attribute-footer',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/formatter/choices/base',
        'oro/mediator',
        'pim/initselect2'
    ],
    function (
        $,
        _,
        BaseForm,
        AttributeManager,
        template,
        AttributeLine,
        AttributeFooter,
        UserContext,
        FetcherRegistry,
        ChoicesFormatter,
        mediator,
        initSelect2
    ) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'add-attribute',
            template: _.template(template),
            defaultOptions: {},
            resultsPerPage: 20,
            selection: [],
            attributeViews: [],
            footerView: null,
            queryTimer: null,

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.defaultOptions = {
                    placeholder: _.__('pim_enrich.form.product.tab.attributes.btn.add_attributes'),
                    title: _.__('pim_enrich.form.product.tab.attributes.info.search_attributes'),
                    buttonTitle: _.__('pim_enrich.form.product.tab.attributes.btn.add'),
                    emptyText: _.__('pim_enrich.form.product.tab.attributes.info.no_available_attributes'),
                    classes: 'pim-add-attributes-multiselect',
                    minimumInputLength: 0,
                    dropdownCssClass: 'add-attribute',
                    closeOnSelect: false
                };
            },

            /**
             * Render this extension
             *
             * @return {Object}
             */
            render: function () {
                this.$el.html(this.template());

                this.initializeSelectWidget();
                this.delegateEvents();

                return this;
            },

            /**
             * Initialize select2 and format elements.
             */
            initializeSelectWidget: function () {
                var $select = this.$('select.select-field');
                var ArrayData = $.fn.select2.amd.require('select2/data/array');
                var Utils = $.fn.select2.amd.require('select2/utils');

                var attributesAdapter = function ($element, options) {
                    attributesAdapter.__super__.constructor.call(this, $element, options);
                };

                Utils.Extend(attributesAdapter, ArrayData);

                attributesAdapter.prototype.query = function (params, callback) {
                    clearTimeout(this.queryTimer);
                    this.queryTimer = setTimeout(function () {
                        var page = 1;
                        if (params.context && params.context.page) {
                            page = params.context.page;
                        }
                        var searchParameters = this.getSelectSearchParameters(params.term, page);

                        AttributeManager.getAttributesForProduct(this.getFormData())
                            .then(function (productAttributes) {
                                searchParameters.options.excluded_identifiers = productAttributes;

                                return FetcherRegistry.getFetcher('attribute').search(searchParameters);
                            })
                            .then(function (attributes) {
                                var choices = _.chain(attributes)
                                    .map(function (attribute) {
                                        var attributeGroup = ChoicesFormatter.formatOne(attribute.group);
                                        var attributeChoice = ChoicesFormatter.formatOne(attribute);
                                        attributeChoice.group = attributeGroup;

                                        return attributeChoice;
                                    })
                                    .value();

                                callback({
                                    results: choices,
                                    pagination: {
                                        more: choices.length === this.resultsPerPage
                                    },
                                    context: {
                                        page: page + 1
                                    }
                                });
                            }.bind(this));
                    }.bind(this), 400);
                }.bind(this);

                var opts = {
                    dropdownCssClass: 'bigdrop add-attribute',
                    /**
                     * Format result (attribute list) method of select2.
                     * This way we can display attributes and their attribute group beside them.
                     */
                    templateResult: function (item) {
                        var line = _.findWhere(this.attributeViews, {attributeCode: item.id});

                        if (undefined === line || null === line) {
                            line = {
                                attributeCode: item.id,
                                attributeView: new AttributeLine({
                                    checked: _.contains(this.selection, item.id),
                                    attributeItem: item
                                })
                            };

                            this.attributeViews.push(line);
                        }

                        return line.attributeView.render().$el;
                    }.bind(this),

                    ajax: {}, // needed to enable infinite scroll
                    dataAdapter: attributesAdapter
                };

                opts = $.extend(true, this.defaultOptions, opts);
                $select = initSelect2.init($select, opts);

                // Close & destroy select2 DOM on change page via hash-navigation
                mediator.once('hash_navigation_request:start', function () {
                    $select.select2('close');
                    $select.select2('destroy');
                });

                // On select2 "selecting" event, we bypass the selection to handle it ourself.
                $select.on('select2:selecting', function (event) {
                    var attributeCode = event.params.args.data.id;
                    var alreadySelected = _.contains(this.selection, attributeCode);

                    if (alreadySelected) {
                        this.selection = _.without(this.selection, attributeCode);
                    } else {
                        this.selection.push(attributeCode);
                    }

                    var line = _.findWhere(this.attributeViews, {attributeCode: attributeCode});
                    line.attributeView.setCheckedCheckbox(!alreadySelected);

                    this.updateSelectedCounter();
                    event.preventDefault();
                }.bind(this));

                this.footerView = new AttributeFooter({
                    buttonTitle: this.defaultOptions.buttonTitle
                });

                this.footerView.on('add-attributes', function () {
                    $select.select2('close');
                    if (this.selection.length > 0) {
                        this.addAttributes();
                    }
                }.bind(this));

                $select.on('select2:open', function () {
                    this.selection = [];
                    this.attributeViews = [];
                    this.updateSelectedCounter();

                    var $menu = $('.bigdrop.add-attribute');
                    $menu.append(this.footerView.render().$el);
                }.bind(this));
            },

            /**
             * Add the saved attributes selection to the product
             */
            addAttributes: function () {
                this.trigger('add-attribute:add', { codes: this.selection });
            },

            /**
             * Update the "selected attributes" counter in the select2 footer
             */
            updateSelectedCounter: function () {
                this.footerView.updateNumberOfAttributes(this.selection.length);
            },

            /**
             * Get attribute fetcher search parameters by giving select2 search term & page
             *
             * @param {string} term
             * @param {int}    page
             *
             * @return {Object}
             */
            getSelectSearchParameters: function (term, page) {
                return {
                    search: term,
                    options: {
                        limit: this.resultsPerPage,
                        page: page,
                        locale: UserContext.get('catalogLocale')
                    }
                };
            }
        });
    }
);
