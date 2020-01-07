define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/datafilter/text-filter',
        'routing',
        'pim/initselect2',
        'pim/user-context',
        'jquery.select2'
    ],
    function(
        $,
        _,
        __,
        TextFilter,
        Routing,
        initSelect2,
        UserContext
    ) {
        'use strict';

        return TextFilter.extend({
            operatorChoices: [],
            choiceUrl: null,
            choiceUrlParams: {},
            emptyChoice: false,
            resultCache: {},
            resultsPerPage: 20,
            choices: [],
            events: {
                'click .AknDropdown-menuLink': '_onSelectOperator'
            },

            initialize: function(options) {
                _.extend(this.events, TextFilter.prototype.events);

                options = options || {};
                if (_.has(options, 'choiceUrl')) {
                    this.choiceUrl = options.choiceUrl;
                }
                if (_.has(options, 'choiceUrlParams')) {
                    this.choiceUrlParams = options.choiceUrlParams;
                }
                if (_.has(options, 'emptyChoice')) {
                    this.emptyChoice = options.emptyChoice;
                }

                if (_.isUndefined(this.emptyValue)) {
                    this.emptyValue = {
                        type: 'in',
                        value: ''
                    };
                }

                this.operatorChoices = {
                    'in': __('pim_datagrid.filters.common.in_list'),
                    'empty': __('pim_datagrid.filters.common.empty'),
                    'not empty': __('pim_datagrid.filters.common.not_empty')
                };

                this.resultCache = {};

                TextFilter.prototype.initialize.apply(this, arguments);
            },

            _onSelectOperator: function(e) {
                const value = $(e.currentTarget).find('.operator_choice').attr('data-value');
                this._highlightDropdown(value, '.operator');

                if (_.contains(['empty', 'not empty'], value)) {
                    this._disableInput();
                } else {
                    this._enableInput();
                }
                e.preventDefault();
            },

            _getSelect2Config: function() {
                var config = {
                    multiple: true,
                    width: '290px',
                    minimumInputLength: 0,
                    formatSelection: function (data, container, escapeMarkup) {
                        const result = $.fn.select2.defaults.formatSelection(data, container, escapeMarkup);
                        if (result !== undefined) {
                            return '<div title="' + result + '">' + result + '</div>';
                        }

                        return result;
                    }.bind(this),
                    formatResult: function(result, container, query, escapeMarkup) {
                        const formerResult = $.fn.select2.defaults.formatResult(result, container, query, escapeMarkup);
                        container.attr('title', result.text);

                        return formerResult;
                    }.bind(this)
                };

                if (this.choiceUrl) {
                    config.ajax = {
                        url: Routing.generate(this.choiceUrl, this.choiceUrlParams),
                        cache: true,
                        data: _.bind(function(term, page) {
                            return {
                                search: term,
                                options: {
                                    limit: this.resultsPerPage,
                                    page: page,
                                    locale: UserContext.get('catalogLocale')
                                }
                            };
                        }, this),
                        results: _.bind(function(data) {
                            this._cacheResults(data.results);
                            data.more = this.resultsPerPage === data.results.length;

                            return data;
                        }, this)
                    };
                } else {
                    config.data = _.map(this.choices, function(choice) {
                        return {
                            id: choice.value,
                            text: choice.label
                        };
                    });
                }

                return config;
            },

            _writeDOMValue: function(value) {
                if (_.contains(['empty', 'not empty'], value.type)) {
                    this._setInputValue(this.criteriaValueSelectors.value, []);
                } else {
                    this._setInputValue(this.criteriaValueSelectors.value, value.value);
                }
                this._highlightDropdown(value.type, '.operator');

                return this;
            },

            _readDOMValue: function() {
                var operator = this.emptyChoice ? this.$('.active .operator_choice').data('value') : 'in';

                return {
                    value: _.contains(['empty', 'not empty'], operator) ? {} : this._getInputValue(this.criteriaValueSelectors.value),
                    type: operator
                };
            },

            /**
             * {@inheritdoc}
             */
            _renderCriteria: function(el) {
                TextFilter.prototype._renderCriteria.apply(this, arguments);

                this.$(this.criteriaValueSelectors.value).addClass('AknTextField--select2');
                initSelect2.init(this.$(this.criteriaValueSelectors.value), this._getSelect2Config());

                this._updateCriteriaHint();
            },

            _onClickCriteriaSelector: function(e) {
                e.stopPropagation();
                $('body').trigger('click');
                if (!this.popupCriteriaShowed) {
                    this._showCriteria();

                    if (_.contains(['empty', 'not empty'], this.getValue().type)) {
                        this._disableInput();
                    } else {
                        initSelect2.init(this.$(this.criteriaValueSelectors.value), this._getSelect2Config())
                            .select2('data', this._getCachedResults(this.getValue().value))
                            .select2('open');
                    }
                } else {
                    this._hideCriteria();
                }
            },

            _onClickCloseCriteria: function() {
                TextFilter.prototype._onClickCloseCriteria.apply(this, arguments);

                this.$(this.criteriaValueSelectors.value).select2('close');
            },

            _onClickOutsideCriteria: function(e) {
                var elem = this.$(this.criteriaSelector);

                if (e.target != $('body').get(0) && e.target !== elem.get(0) && !elem.has(e.target).length) {
                    this._hideCriteria();
                    this.setValue(this._formatRawValue(this._readDOMValue()));
                    e.stopPropagation();
                }
            },

            _onReadCriteriaInputKey: function(e) {
                if (e.which == 13) {
                    this.$(this.criteriaValueSelectors.value).select2('close');
                    this._hideCriteria();
                    this.setValue(this._formatRawValue(this._readDOMValue()));
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
                        if (_.isEmpty(this.choices)) {
                            missingResults.push(id);
                        } else {
                            var choice = _.findWhere(this.choices, { value: id });
                            if (_.isUndefined(choice)) {
                                missingResults.push(id);
                            } else {
                                results.push({ id: choice.value, text: choice.label });
                            }
                        }
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
                var operator = this.$('.active .operator_choice').data('value');
                var type = this.getValue().type;
                if (_.contains(['empty', 'not empty'], operator)) {
                    return this.operatorChoices[operator];
                }

                if (_.contains(['empty', 'not empty'], type)) {
                    return this.operatorChoices[type];
                }

                var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
                return !_.isEmpty(value.value) ? '"' + value.value + '"': this.placeholder;
            },

            /**
             * {@inheritdoc}
             */
            _enableInput: function() {
                this.$(this.criteriaValueSelectors.value).select2(this._getSelect2Config());

                TextFilter.prototype._enableInput.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            _disableInput: function() {
                this.$(this.criteriaValueSelectors.value).val('').select2('destroy');

                TextFilter.prototype._disableInput.apply(this, arguments);
            }
        });
    }
);
