'use strict';

define(
  [
    'jquery',
    'underscore',
    'oro/translator',
    'routing',
    'oro/datafilter/text-filter',
    'pim/formatter/choices/base',
    'pim/user-context',
    'pim/initselect2',
    'pim/i18n',
    'jquery.select2'
  ],
  function(
    $,
    _,
    __,
    Routing,
    TextFilter,
    ChoicesFormatter,
    UserContext,
    initSelect2,
    i18n
  ) {
    return TextFilter.extend({
      operatorChoices: [],
      choiceUrl: null,
      choiceUrlParams: {},
      emptyChoice: false,
      resultsPerPage: 20,
      referenceEntityIdentifier: null,
      events: {
        'click .AknDropdown-menuLink': '_onSelectOperator'
      },
      criteriaValueSelectors: {
        'value': 'input[name="value"]',
        'operator': '.active .operator_choice'
      },

      initialize: function(options) {
        _.extend(this.events, TextFilter.prototype.events);

        if (!_.isUndefined(options)) {
          _.extend(this, _.pick(options, 'choiceUrl', 'choiceUrlParams', 'emptyChoice'));
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
          minimumInputLength: 0
        };

        if (null !== this.choiceUrl) {
          config.ajax = {
            url: Routing.generate(this.choiceUrl, this.choiceUrlParams),
            quietMillis: 250,
            cache: true,
            type: 'PUT',
            params: {contentType: 'application/json;charset=utf-8'},
            data: (term, page) => {
              const selectedRecords = [];
              const searchQuery = {
                channel: UserContext.get('catalogScope'),
                locale: UserContext.get('catalogLocale'),
                size: this.resultsPerPage,
                page: page - 1,
                filters: [
                  {
                    field: 'reference_entity',
                    operator: '=',
                    value: this.choiceUrlParams.referenceEntityIdentifier,
                  },
                  {
                    field: 'code_label',
                    operator: 'IN',
                    value: term,
                  },
                  {
                    field: 'code',
                    operator: 'NOT IN',
                    value: selectedRecords,
                  },
                ],
              };

              return JSON.stringify(searchQuery);
            },
            results: (result) => {
              const items = result.items.map(
                (normalizedRecord) => {
                  return {
                    id: normalizedRecord.code,
                    text: i18n.getLabel(
                      normalizedRecord.labels,
                      UserContext.get('catalogLocale'),
                      normalizedRecord.code
                    ),
                    original: normalizedRecord,
                  };
                }
              );

              return {
                more: this.resultsPerPage === items.length,
                results: items,
              };
            },
          };

          return config;
        }
      },

      _writeDOMValue: function(value) {
        if (_.contains(['empty', 'not empty'], value.type)) {
          this._setInputValue(this.criteriaValueSelectors.value, []);
        } else {
          this._setInputValue(this.criteriaValueSelectors.value, value.value);
        }
        this.$(this.criteriaValueSelectors.operator).data('value', value.type);
        this._highlightDropdown(value.type, '.operator');

        return this;
      },

      _readDOMValue: function() {
        var operator = this.emptyChoice ? this.$(this.criteriaValueSelectors.operator).data('value') : 'in';

        return {
          value: _.contains(['empty', 'not empty'], operator) ?
            {} : this._getInputValue(this.criteriaValueSelectors.value),
          type: operator
        };
      },

      /**
       * {@inheritdoc}
       */
      _renderCriteria: function() {
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
          this.$(this.criteriaValueSelectors.value).select2('open');
        } else {
          this._hideCriteria();
        }
      },

      _onReadCriteriaInputKey: function(e) {
        if (e.which == 13) {
          this.$(this.criteriaValueSelectors.value).select2('close');
          this._hideCriteria();
          this.setValue(this._formatRawValue(this._readDOMValue()));
        }
      },

      _getResults: function(identifiers) {
        var results = [];

        const config = {
          url: Routing.generate(this.choiceUrl, this.choiceUrlParams),
          async: false,
          quietMillis: 250,
          cache: true,
          method: 'PUT',
          params: {contentType: 'application/json;charset=utf-8'},
          data: (() => {
            const searchQuery = {
              channel: UserContext.get('catalogScope'),
              locale: UserContext.get('catalogLocale'),
              size: this.resultsPerPage,
              page: 0,
              filters: [
                {
                  field: 'reference_entity',
                  operator: '=',
                  value: this.choiceUrlParams.referenceEntityIdentifier,
                },
                {
                  field: 'code_label',
                  operator: 'IN',
                  value: identifiers.join(),
                }
              ],
            };

            return JSON.stringify(searchQuery);
          })(),
          success: (result) => {
            results = result.items.map(
              (normalizedRecord) => {
                return {
                  id: normalizedRecord.code,
                  text: i18n.getLabel(normalizedRecord.labels, UserContext.get('catalogLocale'), normalizedRecord.code),
                  original: normalizedRecord,
                };
              }
            );
          },
        };

        $.ajax(config);

        return results;
      },

      _getInputValue: function(input) {
        return this.$(input).select2('val');
      },

      _setInputValue: function(input, value) {
        this.$(input).select2('data', this._getResults(value));

        return this;
      },

      _updateDOMValue: function() {
        var currentValue = this.getValue();
        var data         = this.$(this.criteriaValueSelectors.value).select2('data');
        if (0 === _.difference(currentValue.value, _.pluck(data, 'id')).length) {
          return;
        }

        return this._writeDOMValue(currentValue);
      },

      _formatDisplayValue: function(value) {
        if (_.isEmpty(value.value)) {
          return value;
        }

        return {
          value: _.pluck(
            this.$(this.criteriaValueSelectors.value).select2('data'),
            'text'
          ).join(', ')
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

        return !_.isEmpty(value.value) ? '"' + value.value + '"' : this.placeholder;
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
