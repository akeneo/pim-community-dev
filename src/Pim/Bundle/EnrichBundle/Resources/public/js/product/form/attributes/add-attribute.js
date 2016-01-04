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
        'backbone',
        'underscore',
        'pim/form',
        'pim/attribute-manager',
        'text!pim/template/product/tab/attribute/add-attribute',
        'pim/user-context',
        'pim/fetcher-registry',
        'oro/loading-mask',
        'pim/formatter/choices/base',
        'jquery.multiselect',
        'jquery.multiselect.filter'
    ],
    function (
        $,
        Backbone,
        _,
        BaseForm,
        AttributeManager,
        template,
        UserContext,
        FetcherRegistry,
        LoadingMask,
        ChoicesFormatter
    ) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'add-attribute',
            template: _.template(template),
            defaultOptions: {
                placeholder: _.__('pim_enrich.form.product.tab.attributes.btn.add_attributes'),
                title: _.__('pim_enrich.form.product.tab.attributes.info.search_attributes'),
                buttonTitle: _.__('pim_enrich.form.product.tab.attributes.btn.add'),
                emptyText: _.__('pim_enrich.form.product.tab.attributes.info.no_available_attributes'),
                classes: 'pim-add-attributes-multiselect',
                minimumInputLength: 0,
                width: '300px',
                dropdownCssClass: 'add-attribute',
                closeOnSelect: false
            },
            resultsPerPage: 20,
            selection: [],

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
             * Initialize jQuery multiselect and its filter plugin
             */
            initializeSelectWidget: function () {
                var queryTimer;
                var $select = this.$('input[type="hidden"]');

                var opts = {
                    formatResult: function (item) {
                        var $checkbox = $('<input>', {'type': 'checkbox', 'data-code': item.id});
                        var $attributeLabel = $('<span>', {'class': 'attribute-label'}).text(item.text);
                        var $groupLabel = $('<span>', {'class': 'group-label'}).text(item.group.text);

                        if (_.contains(this.selection, item.id)) {
                            $checkbox.prop('checked', true);
                        }

                        var $div = $('<div>', {'class': 'select2-result-label-attribute'})
                            .append($checkbox)
                            .append($attributeLabel)
                            .append($groupLabel);

                        $div.on('click', function () {
                            $checkbox.prop('checked', _.contains(this.selection, item.id));
                        }.bind(this));

                        return $div;
                    }.bind(this),
                    query: function (options) {
                        window.clearTimeout(queryTimer);
                        queryTimer = window.setTimeout(function () {
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

                select2.on('select2-selecting', function (event) {
                    if (_.contains(this.selection, event.val)) {
                        this.selection = _.without(this.selection, event.val);
                        $('.add-attribute input[data-code="' + event.val + '"]').prop('checked', false);
                    } else {
                        this.selection.push(event.val);
                        $('.add-attribute input[data-code="' + event.val + '"]').prop('checked', true);
                    }

                    this.updateSelectedCounter();
                    event.preventDefault();
                }.bind(this));

                select2.on('select2-open', function () {
                    this.selection = [];
                    this.updateSelectedCounter();
                }.bind(this));

                var $menu = this.$('.select2-drop');

                var $footerContainer = $('<div>', {'class': 'ui-multiselect-footer'});

                var $saveButton = $('<button>', {'class': 'btn btn-small btn-primary pull-right', 'type': 'button'})
                    .append($('<i>', {'class': 'icon-plus'}))
                    .append(this.defaultOptions.buttonTitle)
                    .on('click', function () {
                        $select.select2('close');
                        if (this.selection.length > 0) {
                            this.addAttributes();
                        }
                    }.bind(this));

                var $selectedCount = $('<span>', {'class': 'attribute-counter'});

                $footerContainer.append($selectedCount);
                $footerContainer.append($saveButton);
                $menu.append($footerContainer);
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
                $('.add-attribute .attribute-counter').text(
                    _.__(
                        'pim_enrich.form.product.tab.attributes.info.attributes_selected',
                        {'attributeCount': this.selection.length}
                    )
                );
            },

            /**
             * Get attribute fetcher search parameters by giving select2 search term & page
             *
             * @param {string} term
             * @param {int}    page
             *
             * @returns {Object}
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
