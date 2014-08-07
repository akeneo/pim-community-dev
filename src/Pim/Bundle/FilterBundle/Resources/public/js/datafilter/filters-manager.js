define(
    ['underscore', 'oro/datafilter/filters-manager'],
    function(_, FiltersManager) {
        'use strict';

        return FiltersManager.extend({
            addButtonTemplate: _.template(
                '<select id="add-filter-select" multiple>' +
                    '<%  var groups = {};' +
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
                    '<% }); %>' +
                '</select>'
            ),

            enableFilters: function(filters) {
                if (_.isEmpty(filters)) {
                    return this;
                }
                var optionsSelectors = [];

                _.each(filters, function(filter) {
                    filter.render();
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

            render: function () {
                this.$el.empty();
                var fragment = document.createDocumentFragment();

                _.each(this.filters, function(filter) {
                    if (filter.enabled) {
                        filter.render();
                    } else {
                        filter.hide();
                    }

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

            _initializeSelectWidget: function() {
                FiltersManager.prototype._initializeSelectWidget.apply(this, arguments);

                this.selectWidget.getWidget().addClass('pimmultiselect');
            }
        });
    }
);
