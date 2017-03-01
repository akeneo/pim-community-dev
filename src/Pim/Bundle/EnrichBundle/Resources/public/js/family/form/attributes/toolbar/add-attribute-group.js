'use strict';

/**
 * Add attributes by groups select2 view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/attribute-manager',
        'text!pim/template/form/attribute/add-attribute',
        'pim/common/attribute-group/add/line',
        'pim/common/attribute-group/add/footer',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/formatter/choices/base',
        'oro/mediator',
        'pim/initselect2'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        AttributeManager,
        template,
        LineView,
        FooterView,
        UserContext,
        FetcherRegistry,
        ChoicesFormatter,
        mediator,
        initSelect2
    ) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'AknButtonList-item add-attribute-group',
            template: _.template(template),
            config: {},
            resultsPerPage: 10,
            selection: [],
            itemViews: [],
            footerView: null,
            queryTimer: null,
            footerViewEvent: 'add-attributes-by-groups-btn-clicked',

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = _.extend({}, {
                    select2: {
                        placeholder: 'pim_enrich.form.common.tab.attributes.btn.add_attributes',
                        title: 'pim_enrich.form.common.tab.attributes.info.search_attributes',
                        buttonTitle: 'pim_enrich.form.common.tab.attributes.btn.add',
                        emptyText: 'pim_enrich.form.common.tab.attributes.info.no_available_attributes',
                        classes: 'pim-add-attributes-multiselect',
                        minimumInputLength: 0,
                        dropdownCssClass: 'add-attribute-group',
                        closeOnSelect: false,
                        countTitle: 'pim_enrich.form.common.tab.attributes.info.attributes_groups_selected'
                    },
                    resultsPerPage: this.resultsPerPage,
                    searchParameters: {}
                }, meta.config);

                this.config.select2.placeholder = __(this.config.select2.placeholder);
                this.config.select2.title       = __(this.config.select2.title);
                this.config.select2.buttonTitle = __(this.config.select2.buttonTitle);
                this.config.select2.emptyText   = __(this.config.select2.emptyText);
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
                    dropdownCssClass: 'select2--bigDrop select2--annotedLabels add-attribute-group',
                    formatResult: this.onGetResult.bind(this),
                    query: this.onGetQuery.bind(this)
                };

                opts = $.extend(true, {}, this.config.select2, opts);
                $select = initSelect2.init($select, opts);

                mediator.once('hash_navigation_request:start', function () {
                    $select.select2('close');
                    $select.select2('destroy');
                });

                $select.on('select2-selecting', this.onSelecting.bind(this));

                $select.on('select2-open', this.onSelectOpen.bind(this));

                this.footerView = new FooterView({
                    buttonTitle: this.config.select2.buttonTitle,
                    countTitle: this.config.select2.countTitle,
                    addEvent: this.footerViewEvent
                });

                this.footerView.on(this.footerViewEvent, function () {
                    $select.select2('close');
                    if (0 < this.selection.length) {
                        this.addItems();
                    }
                }.bind(this));

                var $menu = this.$('.select2-drop');

                $menu.append(this.footerView.render().$el);
            },

            /**
             * Trigger an event to expose selected items
             */
            addItems: function () {
                this.getRoot().trigger('add-attribute-group:add', { codes: this.selection });
            },

            /**
             * Gets search parameters
             *
             * @param {string} term
             * @param {int}    page
             *
             * @return {Object}
             */
            getSelectSearchParameters: function (term, page) {
                return $.extend(true, {}, this.config.searchParameters, {
                    search: term,
                    options: {
                        limit: this.resultsPerPage,
                        page: page,
                        locale: UserContext.get('catalogLocale')
                    }
                });
            },

            /**
             * Gets attribute groups to exclude
             * @todo Implement it if we needed in the future
             *
             * @return {Promise}
             */
            getItemsToExclude: function () {
                return $.Deferred().resolve([]);
            },

            /**
             * Updates list of items
             *
             * @param {Object} item
             *
             * @returns {Object}
             */
            onGetResult: function (item) {
                var line = _.findWhere(this.itemViews, {itemCode: item.id});

                if (undefined === line || null === line) {
                    line = {
                        itemCode: item.id,
                        itemView: new LineView({
                            checked: _.contains(this.selection, item.id),
                            item: item
                        })
                    };

                    this.itemViews.push(line);
                }

                return line.itemView.render().$el;
            },

            /**
             * Creates request according to recieved options
             *
             * @param {Object} options
             */
            onGetQuery: function (options) {
                clearTimeout(this.queryTimer);
                this.queryTimer = setTimeout(function () {
                    var page = 1;
                    if (options.context && options.context.page) {
                        page = options.context.page;
                    }
                    var searchParameters = this.getSelectSearchParameters(options.term, page);

                    this.getItemsToExclude()
                        .then(function (excludedAttribute) {
                            searchParameters.options.excluded_identifiers = excludedAttribute;

                            return FetcherRegistry.getFetcher('attribute-group').search(searchParameters);
                        })
                        .then(function (items) {
                            var choices = _.chain(_.sortBy(items, function (item) {
                                return item.sort_order;
                            }))
                                .map(function (item) {
                                    return ChoicesFormatter.formatOne(item);
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
            },

            /**
             * Intercepts default select2 select action
             *
             * @param {Object} event
             */
            onSelecting: function (event) {
                var itemCode = event.val;
                var alreadySelected = _.contains(this.selection, itemCode);

                if (alreadySelected) {
                    this.selection = _.without(this.selection, itemCode);
                } else {
                    this.selection.push(itemCode);
                }

                var line = _.findWhere(this.itemViews, {itemCode: itemCode});
                line.itemView.setCheckedCheckbox(!alreadySelected);

                this.updateSelectedCounter();
                event.preventDefault();
            },

            /**
             * Cleans select2 when open
             */
            onSelectOpen: function () {
                this.selection = [];
                this.itemViews = [];
                this.updateSelectedCounter();
            },

            /**
             * Update counter of selected items
             */
            updateSelectedCounter: function () {
                this.footerView.updateNumberOfItems(this.selection.length);
            }
        });
    }
);

