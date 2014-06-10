define(
    ['jquery', 'underscore', 'oro/datafilter/text-filter', 'routing', 'jquery.select2'],
    function($, _, TextFilter, Routing) {
        'use strict';

        return TextFilter.extend({
            choiceUrl: null,
            choiceUrlParams: {},
            resultCache: {},

            popupCriteriaTemplate: _.template(
                '<div class="choicefilter">' +
                    '<div class="input-prepend">' +
                        '<div class="btn-group">' +
                            '<input type="text" name="value" value=""/>' +
                        '</div>' +
                    '</div>' +
                    '<div class="btn-group">' +
                        '<button type="button" class="btn btn-primary filter-update"><%- _.__("Update") %></button>' +
                    '</div>' +
                '</div>'
            ),

            initialize: function(options) {
                options = options || {};
                if (_.has(options, 'choiceUrl')) {
                    this.choiceUrl = options.choiceUrl;
                }
                if (_.has(options, 'choiceUrlParams')) {
                    this.choiceUrlParams = options.choiceUrlParams;
                }

                TextFilter.prototype.initialize.apply(this, arguments);
            },

            _renderCriteria: function() {
                TextFilter.prototype._renderCriteria.apply(this, arguments);

                var self = this;
                var $select = this.$(this.criteriaValueSelectors.value);

                var options = {
                    multiple: true,
                    allowClear: false,
                    width: '290px',
                    minimumInputLength: 1
                };
                options.ajax = {
                    url: Routing.generate(this.choiceUrl, this.choiceUrlParams),
                    cache: true,
                    data: function(term) {
                        return {
                            search: term
                        };
                    },
                    results: function(data) {
                        self._cacheResults(data.results);
                        return data;
                    }
                };
                $select.select2(options);
            },

            _cacheResults: function (results) {
                _.each(results, function (result) {
                    this.resultCache[result.id] = result.text;
                }, this);
            },

            _getCachedResults: function(ids) {
                var results = [],
                    missingResults = [];

                _.each(ids, function(id) {
                    if (_.isUndefined(this.resultCache[id])) {
                        missingResults.push(id);
                    } else {
                        results.push({ id: id, text: this.resultCache[id] });
                    }
                }, this);

                _.each(missingResults, function(id) {
                    // TODO: get the label from the server
                    results.push({ id: id, text: id });
                });

                return results;
            },

            _getInputValue: function(input) {
                return this.$(input).select2('val');
            },

            _setInputValue: function(input, value) {
                this.$(input).select2('data', this._getCachedResults(value));

                return this;
            },

            _updateDOMValue: function() {
                return this._writeDOMValue(this.getValue());
            },

            _formatDisplayValue: function(value) {
                if (_.isEmpty(value.value)) {
                    return value;
                }

                return {
                    value: _.pluck(this._getCachedResults(value.value), 'text').join(', ')
                };
            },

            _getCriteriaHint: function() {
                var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
                return !_.isEmpty(value.value) ? value.value : this.placeholder;
            }
        });
    }
);
