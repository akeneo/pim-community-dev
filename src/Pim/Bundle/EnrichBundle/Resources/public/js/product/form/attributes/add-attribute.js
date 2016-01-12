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
        'pim/formatter/choices/base'
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
        ChoicesFormatter
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
                var $select = this.$('input[type="hidden"]');

                var opts = {
                    /**
                     * Format result (attribute list) method of select2.
                     * This way we can display attributes and their attribute group beside them.
                     */
                    formatResult: function (item) {
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

                    /**
                     * The query function called by select2 when searching for attributes.
                     *
                     * We prepare the query (ask for server to exlude product attributes), and
                     * handles its response with ChoicesFormatter (for i18n label translation)
                     */
                    query: function (options) {
                        clearTimeout(this.queryTimer);
                        this.queryTimer = setTimeout(function () {
                            var page = 1;
                            if (options.context && options.context.page) {
                                page = options.context.page;
                            }
                            var searchParameters = this.getSelectSearchParameters(options.term, page);

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

                                    options.callback({
                                        results: choices,
                                        more: choices.length === this.resultsPerPage,
                                        context: {
                                            page: page + 1
                                        }
                                    });
                                }.bind(this));
                        }.bind(this), 400);
                    }.bind(this)
                };

                opts = $.extend(true, this.defaultOptions, opts);

                var select2 = $select.select2(opts);

                // On select2 "selecting" event, we bypass the selection to handle it ourself.
                select2.on('select2-selecting', function (event) {
                    var attributeCode = event.val;
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

                select2.on('select2-open', function () {
                    this.selection = [];
                    this.attributeViews = [];
                    this.updateSelectedCounter();
                }.bind(this));

                var $menu = this.$('.select2-drop');

                this.footerView = new AttributeFooter({
                    buttonTitle: this.defaultOptions.buttonTitle
                });

                this.footerView.on('add-attributes', function () {
                    $select.select2('close');
                    if (this.selection.length > 0) {
                        this.addAttributes();
                    }
                }.bind(this));

                $menu.append(this.footerView.render().$el);
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
