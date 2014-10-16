define(
    ['underscore', 'oro/datafilter/filters-manager', 'oro/multiselect-decorator'],
    function(_, FiltersManager, MultiselectDecorator) {
        'use strict';

        var SELECT_OPEN_TEMPLATE = '<select id="add-filter-select" multiple>',
            SELECT_CLOSE_TEMPLATE = '</select>',
            LOADING_TEMPLATE = '<option disabled>Loading</option>',
            OPTIONS_TEMPLATE = '<%  var groups = {};' +
                        '_.each(filters, function(filter) {' +
                            'if (filter.group) {' +
                                'groups[filter.groupOrder !== null ? filter.groupOrder : "last"] = filter.group;' +
                            '} else {' +
                                'filter.group = _.__("system_filter_group");' +
                                'groups[-1] = filter.group;' +
                            '}' +
                       '});' +
                    '%>' +
                    '<% var groups = _.sortBy(groups, function(group, index) {return index;}); %>' +
                    '<% _.each(groups, function (group) { %>' +
                        '<optgroup label="<%= group %>">' +
                            '<% _.each(filters, function (filter, name) { %>' +
                                '<% if (filter.group == group) { %>' +
                                    '<option value="<%= name %>" <% if (filter.enabled) { %>selected<% } %>>' +
                                        '<%= filter.label %>' +
                                    '</option>' +
                                    '<% } %>' +
                            '<% }); %>' +
                        '</optgroup>' +
                    '<% }); %>';
        return FiltersManager.extend({
            filtersLoaded: false,
            createFilterCallback: null,
            metadataUrl: null,
            optionsTemplate: null,
            /**
             * {@inheritdoc}
             */
            initialize: function(options) {
                if (options.metadataUrl) {
                    this.metadataUrl = options.metadataUrl;
                    this.addButtonTemplate = _.template(
                        SELECT_OPEN_TEMPLATE + LOADING_TEMPLATE + SELECT_CLOSE_TEMPLATE
                    );
                    this.optionsTemplate = _.template(OPTIONS_TEMPLATE);
                    this.createFilterCallback = options.callback;
                } else {
                    this.addButtonTemplate = _.template(
                        SELECT_OPEN_TEMPLATE + OPTIONS_TEMPLATE + SELECT_CLOSE_TEMPLATE
                    );
                }
                FiltersManager.prototype.initialize.call(this, options);
            },
            /**
             * {@inheritdoc}
             */
            _initializeSelectWidget: function() {
                if (this.metadataUrl) {
                    this.selectWidget = new MultiselectDecorator({
                        element: this.$(this.filterSelector),
                        parameters: {
                            multiple: true,
                            selectedList: 0,
                            selectedText: this.addButtonHint,
                            classes: 'filter-list select-filter-widget',
                            open: $.proxy(function() {
                                this.loadFilters();
                                this.selectWidget.onOpenDropdown();
                                this._setDropdownWidth();
                                this._updateDropdownPosition();
                            }, this)
                        }
                    });

                    this.selectWidget.setViewDesign(this);
                    this.$('.filter-list span:first').replaceWith(
                        '<a id="add-filter-button" href="javascript:void(0);">' + this.addButtonHint +'</a>'
                    );
                } else {
                    FiltersManager.prototype._initializeSelectWidget.apply(this, arguments);
                }

                this.selectWidget.getWidget().addClass('pimmultiselect');
            },

            /**
             * {@inheritdoc}
             */
            enableFilters: function(filters) {
                if (!this.metadataUrl) {
                    FiltersManager.prototype.enableFilters.apply(this, arguments);

                    return;
                }

                if (_.isEmpty(filters)) {
                    return this;
                }
                var optionsSelectors = [];

                _.each(filters, function(filter) {
                    if (!filter.rendered) {
                        filter.render();
                        this.$el.append(filter.$el.get(0));
                        filter.rendered = true;
                        this.listenTo(filter, "update", this._onFilterUpdated);
                        this.listenTo(filter, "disable", this._onFilterDisabled);
                    }
                    filter.enable();
                    optionsSelectors.push('option[value="' + filter.name + '"]:not(:selected)');
                }, this);

                var options = this.$(this.filterSelector).find(optionsSelectors.join(','));
                if (options.length) {
                    options.attr('selected', true);
                }

                if (optionsSelectors.length) {
                    this.selectWidget.multiselect('refresh');
                }

                return this;
            },
            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.metadataUrl) {
                    FiltersManager.prototype.render.apply(this, arguments);

                    return this;
                }

                this.$el.empty();
                var fragment = document.createDocumentFragment();

                _.each(this.filters, function(filter) {
                    filter.render();
                    filter.rendered = true;
                    fragment.appendChild(filter.$el.get(0));
                }, this);

                this.trigger("rendered");

                if (_.isEmpty(this.filters)) {
                    this.$el.hide();
                } else {
                    this.$el.append(this.addButtonTemplate({filters: this.filters}));
                    this.$el.append(fragment);
                    this._initializeSelectWidget();
                }

                return this;
            },
            /**
             * Loads the filters
             * 
             * @returns {undefined}
             */
            loadFilters: function() {
                if (this.filtersLoaded) {
                    return;
                }
                $.get(
                    this.metadataUrl,
                    $.proxy(function (data) {
                        this.initializeFilters(data.metadata.filters);
                    }, this)
                );
            },
            /**
             * Initializes the filters when loaded from AJAX
             */
            initializeFilters: function(data) {
                var self = this;
                this.filters = {};
                this.filtersLoaded = true;
                _.each(data, function (options) {
                    if (_.has(options, 'name') && _.has(options, 'type')) {
                        if (!_.has(self.filters, options.name)) {
                            self.filters[options.name] = self.createFilterCallback(options);
                        }
                    }
                });
                this.selectWidget.element.html(this.optionsTemplate({filters: this.filters}));
                this.selectWidget.multiselect("refresh");
                this._setDropdownWidth();
                this._updateDropdownPosition();
            },

        });
    }
);
