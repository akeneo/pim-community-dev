define(
    [
        'oro/translator',
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'oro/multiselect-decorator',
        'pim/template/datagrid/add-filter-select'
    ],
    function(
        __,
        $,
        _,
        Backbone,
        mediator,
        MultiselectDecorator,
        addFilterSelectTemplate
    ) {
    /**
     * View that represents all grid filters
     *
     * @export  oro/datafilter/filters-manager
     * @class   oro.datafilter.FiltersManager
     * @extends Backbone.View
     *
     * @event updateList    on update of filter list
     * @event updateFilter  on update data of specific filter
     * @event disableFilter on disable specific filter
     */
    return Backbone.View.extend({
        /**
         * List of filter objects
         *
         * @property
         */
        filters: {},

        /**
         * Container tag name
         *
         * @property
         */
        tagName: 'div',

        /**
         * Display the 'manage filters' button or not
         *
         * @property
         */
        displayManageFilters: function() {
            return _.result(this.options, 'displayManageFilters', true);
        },

        /**
         * Displays the filters as column or not
         *
         * @property
         */
        filtersAsColumn: function() {
            return _.result(this.options, 'filtersAsColumn', false);
        },

        /**
         * Container classes
         *
         * @property
         */
        className: function () {
            var name = 'AknFilterBox filter-box oro-clearfix-width';
            if (true !== this.filtersAsColumn()) {
                name += ' AknFilterBox--search';
            }

            return name;
        },

        /**
         * Filter list template
         *
         * @property
         */
        addButtonTemplate: _.template(addFilterSelectTemplate),

        /**
         * Filter list input selector
         *
         * @property
         */
        filterSelector: '#add-filter-select',

        /**
         * Select widget object
         *
         * @property {oro.MultiselectDecorator}
         */
        selectWidget: null,

        /**
         * ImportExport button selector
         *
         * @property
         */
        buttonSelector: '.ui-multiselect.filter-list',

        /** @property */
        events: {
            'change #add-filter-select': '_onChangeFilterSelect'
        },

        /**
         * Initialize filter list options
         *
         * @param {Object} options
         * @param {Object} [options.filters]
         * @param {Boolean} [options.displayManageFilters]
         */
        initialize: function (options) {
            if (options.filters) {
                this.filters = options.filters;
            }

            _.each(this.filters, function (filter) {
                this.listenTo(filter, 'update', this._onFilterUpdated);
                this.listenTo(filter, 'disable', this._onFilterDisabled);
            }, this);

            Backbone.View.prototype.initialize.apply(this, arguments);

            // destroy events bindings
            mediator.once('hash_navigation_request:start', function () {
                _.each(this.filters, function (filter) {
                    this.stopListening(filter, 'update', this._onFilterUpdated);
                    this.stopListening(filter, 'disable', this._onFilterDisabled);
                }, this);
            }, this);
        },

        /**
         * Triggers when filter is updated
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @protected
         */
        _onFilterUpdated: function (filter) {
            this.trigger('updateFilter', filter);
        },

        /**
         * Triggers when filter is disabled
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @protected
         */
        _onFilterDisabled: function (filter) {
            this.trigger('disableFilter', filter);
            this.disableFilter(filter);
        },

        /**
         * Returns list of filter raw values
         */
        getValues: function () {
            var values = {};
            _.each(this.filters, function (filter) {
                if (filter.enabled) {
                    values[filter.name] = filter.getValue();
                }
            }, this);

            return values;
        },

        /**
         * Sets raw values for filters
         */
        setValues: function (values) {
            _.each(values, function (value, name) {
                if (_.has(this.filters, name)) {
                    this.filters[name].setValue(value);
                }
            }, this);
        },

        /**
         * Triggers when filter select is changed
         *
         * @protected
         */
        _onChangeFilterSelect: function () {
            this.trigger('updateList', this);
            this._processFilterStatus();
        },

        /**
         * Enable filter
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @return {*}
         */
        enableFilter: function (filter) {
            return this.enableFilters([filter]);
        },

        /**
         * Disable filter
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @return {*}
         */
        disableFilter: function (filter) {
            return this.disableFilters([filter]);
        },

        /**
         * Enable filters
         *
         * @param filters []
         * @return {*}
         */
        enableFilters: function (filters) {
            if (_.isEmpty(filters)) {
                return this;
            }
            var optionsSelectors = [];

            _.each(filters, function (filter) {
                filter.enable();
                optionsSelectors.push(`option[value="${filter.name}"]:not(:selected)`);
            }, this);

            var options = this.$(this.filterSelector).find(optionsSelectors.join(','));
            if (options.length) {
                options.attr('selected', true);
            }

            if (this.displayManageFilters() && optionsSelectors.length) {
                this.selectWidget.multiselect('refresh');
            }

            return this;
        },

        /**
         * Disable filters
         *
         * @param filters []
         * @return {*}
         */
        disableFilters: function (filters) {
            if (_.isEmpty(filters)) {
                return this;
            }
            var optionsSelectors = [];

            _.each(filters, function (filter) {
                filter.disable();
                optionsSelectors.push(`option[value="${filter.name}"]:selected`);
            }, this);

            var options = this.$(this.filterSelector).find(optionsSelectors.join(','));
            if (options.length) {
                options.removeAttr('selected');
            }

            if (this.displayManageFilters() && optionsSelectors.length) {
                this.selectWidget.multiselect('refresh');
            }

            return this;
        },

        /**
         * Render filter list
         *
         * @return {*}
         */
        render: function () {
            this.$el.empty();
            var fragment = document.createDocumentFragment();

            _.each(this.filters, function (filter) {
                if (!filter.enabled) {
                    filter.hide();
                }
                if (filter.enabled) {
                    filter.render();
                }
                if (filter.$el.length > 0) {
                    fragment.appendChild(filter.$el.get(0));
                }
            }, this);

            this.trigger('rendered');

            if (_.isEmpty(this.filters)) {
                this.$el.hide();
            } else {
                if (this.displayManageFilters()) {
                    this.$el.append(this.addButtonTemplate(
                        {
                            filters: this.filters,
                            systemFilterGroup: __('system_filter_group')
                        }
                    ));
                }
                this.$el.append(fragment);
                this._initializeSelectWidget();
            }

            return this;
        },

        /**
         * Initialize multiselect widget
         *
         * @protected
         */
        _initializeSelectWidget: function () {
            if (!this.displayManageFilters()) {
                return;
            }

            this.selectWidget = new MultiselectDecorator({
                element: this.$(this.filterSelector),
                parameters: {
                    multiple: true,
                    selectedList: 0,
                    classes: 'AknFilterBox-addFilterButton filter-list select-filter-widget',
                    open: $.proxy(function () {
                        if (this.$el.is(':visible')) {
                            this.selectWidget.onOpenDropdown();
                            this._setDropdownWidth();
                            this._updateDropdownPosition();
                        }
                    }, this)
                }
            });

            this.selectWidget.setViewDesign(this);
            this.selectWidget.getWidget().addClass('pimmultiselect');

            this.$('.filter-list span:first').replaceWith(
                '<a id="add-filter-button" href="javascript:void(0);"></a>'
            );
        },

        /**
         * Set design for select dropdown
         *
         * @protected
         */
        _setDropdownWidth: function () {
            var widget = this.selectWidget.getWidget();
            var requiredWidth = this.selectWidget.getMinimumDropdownWidth() + 24;
            widget.width(requiredWidth).css('min-width', requiredWidth + 'px');
            widget.find('input[type="search"]').width(requiredWidth - 22);
        },

        /**
         * Activate/deactivate all filter depends on its status
         *
         * @protected
         */
        _processFilterStatus: function () {
            var activeFilters = this.$(this.filterSelector).val();

            _.each(this.filters, function (filter, name) {
                if (!filter.enabled && _.indexOf(activeFilters, name) != -1) {
                    this.enableFilter(filter);
                } else if (filter.enabled && _.indexOf(activeFilters, name) == -1) {
                    this.disableFilter(filter);
                }
            }, this);

            this._updateDropdownPosition();
        },

        /**
         * Set dropdown position according to current element
         *
         * @protected
         */
        _updateDropdownPosition: function () {
            var button = this.$(this.buttonSelector);
            var buttonPosition = button.offset();
            var widgetWidth = this.selectWidget.getWidget().outerWidth();
            var windowWidth = $(window).width();
            var widgetLeftOffset = buttonPosition.left;
            if (buttonPosition.left + widgetWidth > windowWidth) {
                widgetLeftOffset = buttonPosition.left + button.outerWidth() - widgetWidth;
            }

            this.selectWidget.getWidget().css({
                top: buttonPosition.top + button.outerHeight(),
                left: widgetLeftOffset
            });
        }
    });
});
