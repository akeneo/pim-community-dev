define(
    [
        'oro/translator',
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'oro/multiselect-decorator',
        'pim/template/datagrid/add-filter-select',
        'pim/template/datagrid/add-filter-button',
        'pim/template/datagrid/done-container',
        'pim/template/datagrid/done-button'
    ],
    function(
        __,
        $,
        _,
        Backbone,
        mediator,
        MultiselectDecorator,
        addFilterSelectTemplate,
        addFilterButtonTemplate,
        doneContainerTemplate,
        doneButtonTemplate
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
        displayAsPanel: false,

        /**
         * List of filter objects
         *
         * @property
         */
        filters: {},

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

        addButtonTemplate: _.template(addFilterSelectTemplate),
        addFilterButtonTemplate: _.template(addFilterButtonTemplate),
        doneContainerTemplate: _.template(doneContainerTemplate),
        doneButtonTemplate: _.template(doneButtonTemplate),

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
            this.displayAsPanel = options.displayAsPanel;

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
         * Closes the panel when the user clicks on Done button
         */
        _onClose() {
            this.selectWidget.multiselect('close');
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
                options.prop('selected', true);
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
         * Container classes
         *
         * @property
         */
        className: function () {
            if (this.options.renderFilterList) {
                return 'AknFilterBox filter-box oro-clearfix-width AknFilterBox--search';
            }
        },

        /**
         * Render filter list
         *
         * @return {*}
         */
        render: function () {
            this.$el.empty();
            var fragment = document.createDocumentFragment();

            // Only used for grids within tabs
            if (this.options.renderFilterList) {
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
            }

            this.trigger('rendered');

            if (_.isEmpty(this.filters)) {
                this.$el.hide();
            } else {
                this._fillEmptyGroupFilters();
                this.$el.append(fragment);
                if (this.displayManageFilters()) {
                    this.$el.append(this.addButtonTemplate(
                        {
                            filters: this.filters,
                            groups: this._getSortedGroups(),
                        }
                    ));
                }
                this._initializeSelectWidget();
            }

            return this;
        },

        /**
         * Returns the groups belonging to the set of filters.
         * It returns an array beginning with the system one (containing filters without group), then the groups
         * having an sort order, and finally the groups without any sort order.
         */
        _getSortedGroups: function () {
            let groups = [];
            let unsortedGroups = [];
            Object.values(this.filters).forEach((filter) => {
                if (filter.group !== __('pim_datagrid.column_configurator.system_group')) {
                    if (filter.groupOrder !== null) {
                        if (groups.filter((group) => {
                            return group.label === filter.group;
                        }).length === 0) {
                            groups.push({label: filter.group, order: filter.groupOrder});
                        }
                    } else {
                        if (unsortedGroups.indexOf(filter.group) <= -1) {
                            unsortedGroups.push(filter.group);
                        }
                    }
                }
            });

            return [__('pim_datagrid.filters.system')].concat(groups.sort((group1, group2) => {
                return group1.order - group2.order
            }).map((group) => {
                return group.label;
            })).concat(unsortedGroups);
        },

        /**
         * Fills the filters having no group to put it into the "System" one.
         * This method is just for display, it's not registered in database, as the "system" group does not exist.
         */
        _fillEmptyGroupFilters: function () {
            Object.keys(this.filters).forEach((filterKey) => {
                if (this.filters[filterKey].group === '' ||
                    this.filters[filterKey].group === null ||
                    this.filters[filterKey].group === undefined
                ) {
                    this.filters[filterKey].group = __('pim_datagrid.filters.system');
                }
            });
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

            let multiselectParameters = {
                multiple: true,
                selectedList: 0,
                classes: 'AknFilterBox-addFilterButton AknFilterBox-addFilterButton--asPanel filter-list select-filter-widget',
                position: {
                    at: 'right top',
                    my: 'right top',
                }
            };

            if (!this.displayAsPanel) {
                multiselectParameters.classes = 'AknFilterBox-addFilterButton filter-list select-filter-widget';
                multiselectParameters.beforeopen = () => {
                    this.selectWidget.getWidget().css({ left: this._getLeftStartPosition() });
                    this._addDoneButton();

                    return true;
                };
                multiselectParameters.open = () => {
                    if (this.$el.is(':visible')) {
                        this.selectWidget.onOpenDropdown();
                        this._updateDropdownPosition();
                    }
                };
                multiselectParameters.beforeclose = () => {
                    if (null === this.selectWidget.getWidget() ||
                        0 === this.selectWidget.getWidget().length ||
                        this.selectWidget.getWidget().position().left <= this._getLeftStartPosition()) {
                        return true;
                    }

                    this.selectWidget.getWidget().css({ left: this._getLeftEndPosition() + 'px' });
                    this.selectWidget.getWidget().css({ left: this._getLeftStartPosition() + 'px' });
                    setTimeout(() => this.selectWidget.multiselect('close'), 500);

                    return false;
                };
                multiselectParameters.position = {
                    left: this._getLeftStartPosition()
                };
            }

            this.selectWidget = new MultiselectDecorator({
                element: this.$(this.filterSelector),
                parameters: multiselectParameters,
            });

            this.selectWidget.setViewDesign(this);
            this.selectWidget.getWidget().addClass('pimmultiselect');

            this.$('.filter-list span:first').replaceWith(
                this.addFilterButtonTemplate({
                    label: __('pim_datagrid.filters.label')
                })
            );

        },

        /**
         * Adds a done button in the bottom of the multiselect
         */
        _addDoneButton() {
            if (!this.selectWidget.getWidget().find('.close').length) {
                const button = $(this.doneButtonTemplate({
                    label: __('pim_common.done')
                }));
                button.on('click', () => this._onClose());
                const container = $(this.doneContainerTemplate());
                container.append(button);
                this.selectWidget.getWidget().append(container);
            }
        },

        /**
         * Activate/deactivate all filter depends on its status
         *
         * @protected
         */
        _processFilterStatus: function () {
            const activeFilters = this.$(this.filterSelector).val();

            _.each(this.filters, function (filter, name) {
                if (!filter.enabled && _.indexOf(activeFilters, name) !== -1) {
                    this.enableFilter(filter);
                } else if (filter.enabled && _.indexOf(activeFilters, name) === -1) {
                    this.disableFilter(filter);
                }
            }, this);
        },

        /**
         * Set dropdown position according to current element. This methods animates the panel to be displayed
         * like the other columns.
         */
        _updateDropdownPosition: function () {
            this.selectWidget.getWidget().css({ left: this._getLeftStartPosition() + 'px' });
            this.selectWidget.getWidget().css({ left: this._getLeftEndPosition() + 'px' });
        },

        /**
         * Get the width of the main column and the header
         * @return {number}
         */
        _getOffsetWidth() {
            const headerWidth = $('.AknHeader').width();
            const mainColumn = $('.AknDefault-contentWithColumn .AknColumn');
            const mainColumnWidth = 0 !== mainColumn.length ? mainColumn.width() : 0;

            return headerWidth + mainColumnWidth;
        },

        /**
         * Returns the left absolute position for the animation start
         *
         * @returns {number}
         */
        _getLeftStartPosition() {
            return this._getOffsetWidth() - 300;
        },

        /**
         * Returns the left absolute position for the animation end
         *
         * @returns {number}
         */
        _getLeftEndPosition() {
            return this._getOffsetWidth();
        }
    });
});
