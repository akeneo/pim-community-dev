define(
    ['jquery', 'underscore', 'oro/datafilter/text-filter', 'routing', 'jquery.select2'],
    function($, _, TextFilter, Routing) {
        'use strict';

        return TextFilter.extend({
            choiceUrl: null,
            choiceUrlParams: {},
            resultCache: {},
            resultsPerPage: 200,
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

                var $select = this.$(this.criteriaValueSelectors.value);

                var options = {
                    multiple: true,
                    allowClear: false,
                    width: '290px',
                    minimumInputLength: 0
                };

                options.ajax = {
                    url: Routing.generate(this.choiceUrl, this.choiceUrlParams),
                    cache: true,
                    data: _.bind(function(term, page) {
                        return {
                            search: term,
                            options: {
                                limit: this.resultsPerPage,
                                page: page
                            }
                        };
                    }, this),
                    results: _.bind(function(data, page) {
                        this._cacheResults(data.results);
                        data.more = this.resultsPerPage === data.results.length;

                        return data;
                    }, this)
                };
                $select.select2(options);
            },

            _onClickCriteriaSelector: function(e) {
                e.stopPropagation();
                $('body').trigger('click');
                if (!this.popupCriteriaShowed) {
                    this._showCriteria();
                    this.$(this.criteriaValueSelectors.value).select2('open');
                } else {
                    this._hideCriteria();
                }
            },

            _onClickCloseCriteria: function() {
                TextFilter.prototype._onClickCloseCriteria.apply(this, arguments);

                this.$(this.criteriaValueSelectors.value).select2('close');
            },

            _onClickOutsideCriteria: function(e) {
                var elem = this.$(this.criteriaSelector)

                if (e.target != $('body').get(0) && e.target !== elem.get(0) && !elem.has(e.target).length) {
                    this._hideCriteria();
                    this.setValue(this._formatRawValue(this._readDOMValue()));
                    e.stopPropagation();
                }
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

                if (missingResults.length) {
                    var params = { options: { ids: missingResults } };

                    $.ajax({
                        url: Routing.generate(this.choiceUrl, this.choiceUrlParams) + '&' + $.param(params),
                        success: _.bind(function(data) {
                            this._cacheResults(data.results);
                            results = _.union(results, data.results);
                        }, this),
                        async: false
                    });
                }

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
