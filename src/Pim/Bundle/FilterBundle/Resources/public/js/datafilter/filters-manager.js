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

            _initializeSelectWidget: function() {
                FiltersManager.prototype._initializeSelectWidget.apply(this, arguments);

                this.selectWidget.getWidget().addClass('pimmultiselect');
            }
        });
    }
);
