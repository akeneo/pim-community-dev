'use strict';

/**
 * Common add select extension view
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
        'pim/template/form/add-select/select',
        'pim/form',
        'pim/common/add-select/line',
        'pim/common/add-select/footer',
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
        template,
        BaseForm,
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
            targetElement: 'input[type="hidden"]',
            className: null,
            mainFetcher: null,
            template: _.template(template),
            lineView: LineView,
            footerView: FooterView,
            config: {},
            resultsPerPage: null,
            selection: [],
            itemViews: [],
            footerViewInstance: null,
            queryTimer: null,
            addEvent: null,
            disableEvent: null,
            enableEvent: null,
            disabled: false,
            defaultConfig: {
                select2: {
                    placeholder: 'pim_common.add',
                    title: '',
                    buttonTitle: '',
                    emptyText: '',
                    classes: '',
                    minimumInputLength: 0,
                    dropdownCssClass: '',
                    closeOnSelect: false,
                    countTitle: 'pim_enrich.form.common.base-add-select.label.select_count'
                },
                resultsPerPage: 10,
                searchParameters: {},
                mainFetcher: null,
                events: {
                    disable: null,
                    enable: null,
                    add: null
                }
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = $.extend(true, {}, this.defaultConfig, meta.config);

                if (_.isNull(this.config.mainFetcher)) {
                    throw new Error('Fetcher code must be provided in config');
                }

                this.config.select2.placeholder = __(this.config.select2.placeholder);
                this.config.select2.title       = __(this.config.select2.title);
                this.config.select2.buttonTitle = __(this.config.select2.buttonTitle);
                this.config.select2.emptyText   = __(this.config.select2.emptyText);

                this.resultsPerPage = this.config.resultsPerPage;
                this.mainFetcher    = this.config.mainFetcher;
                this.className      = this.config.className;

                this.disableEvent = this.config.events.disable;
                this.enableEvent  = this.config.events.enable;
                this.addEvent     = this.config.events.add;
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                if (!_.isNull(this.enableEvent) && !_.isNull(this.disableEvent)) {
                    mediator.on(
                        this.disableEvent,
                        this.onDisable.bind(this)
                    );

                    mediator.on(
                        this.enableEvent,
                        this.onEnable.bind(this)
                    );
                }

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Render this extension
             *
             * @return {Object}
             */
            render: function () {
                this.$el.html(this.template());

                this.$('input[type="hidden"]').prop('readonly', this.disabled);

                this.initializeSelectWidget();
                this.delegateEvents();

                return this;
            },

            /**
             * Initialize select2 and format elements.
             */
            initializeSelectWidget: function () {
                var $select = this.$(this.targetElement);

                var opts = {
                    dropdownCssClass: 'select2--bigDrop select2--annotedLabels ' + this.config.select2.dropdownCssClass,
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

                this.footerViewInstance = new this.footerView({
                    buttonTitle: this.config.select2.buttonTitle,
                    countTitle: this.config.select2.countTitle,
                    addEvent: this.addEvent
                });

                this.footerViewInstance.on(this.addEvent, function () {
                    $select.select2('close');
                    if (0 < this.selection.length) {
                        this.addItems();
                    }
                }.bind(this));

                var $menu = this.$('.select2-drop');

                $menu.append(this.footerViewInstance.render().$el);
            },

            /**
             * Triggers configured event with items codes selected
             */
            addItems: function () {
                this.getRoot().trigger(this.addEvent, { codes: this.selection });
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
             * Gets items to exclude
             *
             * @return {Promise}
             */
            getItemsToExclude: function () {
                return $.Deferred().resolve([]);
            },

            /**
             * @param {Object} items
             *
             * @return {Object}
             */
            prepareChoices: function (items) {
                return _.chain(_.sortBy(items, function (item) {
                    return item.sort_order;
                })).map(function (item) {
                    return ChoicesFormatter.formatOne(item);
                }).value();
            },

            /**
             * Formats and updates list of items
             *
             * @param {Object} item
             *
             * @return {Object}
             */
            onGetResult: function (item) {
                var line = _.findWhere(this.itemViews, {itemCode: item.id});

                if (_.isUndefined(line) || _.isNull(line)) {
                    line = {
                        itemCode: item.id,
                        itemView: new this.lineView({
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

                    this.fetchItems(searchParameters)
                        .then(function (items) {
                            var choices = this.prepareChoices(items);

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
             * Fetches items from the backend.
             *
             * @param {Object} searchParameters
             *
             * @return {Promise}
             */
            fetchItems: function (searchParameters) {
                return this.getItemsToExclude()
                    .then(function (identifiersToExclude) {
                        searchParameters.options.excluded_identifiers = identifiersToExclude;

                        return FetcherRegistry.getFetcher(this.mainFetcher).search(searchParameters);
                    }.bind(this));
            },

            /**
             * Intercepts default select2 selecting event and handles it
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
                this.footerViewInstance.updateNumberOfItems(this.selection.length);
            },

            /**
             * Disable callback
             */
            onDisable: function () {
                this.disabled = true;
                this.render();
            },

            /**
             * Enable callback
             */
            onEnable: function () {
                this.disabled = false;
                this.render();
            }
        });
    }
);

