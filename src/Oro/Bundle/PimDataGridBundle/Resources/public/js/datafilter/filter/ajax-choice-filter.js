define(
    ['jquery', 'underscore', 'oro/datafilter/multiselect-filter', 'routing'],
    function($, _, MultiSelectFilter, Routing) {
        'use strict';

        return MultiSelectFilter.extend({
            choicesFetched: false,
            choiceUrl: null,
            choiceUrlParams: {},

            initialize: function(options) {
                options = options || {};
                if (_.has(options, 'choiceUrl')) {
                    this.choiceUrl = options.choiceUrl;
                }
                if (_.has(options, 'choiceUrlParams')) {
                    this.choiceUrlParams = options.choiceUrlParams;
                }

                MultiSelectFilter.prototype.initialize.apply(this, arguments);
            },

            render: function () {
                var options =  this.choices.slice(0);
                this.$el.empty();

                if (this.populateDefault) {
                    options.unshift({value: '', label: this.placeholder});
                }

                this.$el.append(
                    this.template({
                        label: this.label,
                        showLabel: this.showLabel,
                        options: options,
                        placeholder: this.placeholder,
                        nullLink: this.nullLink,
                        canDisable: this.canDisable,
                        emptyValue: this.emptyValue
                    })
                );

                if (this.value.value) {
                    _.each(this.value.value, function(item) {
                        this.$(this.inputSelector).find('option[value="' + item + '"]').prop('selected', true);
                    }, this);
                }

                this._initializeSelectWidget();

                return this;
            },

            show: function() {
                if (!this.choicesFetched && !this.choices.length && this.choiceUrl) {
                    var url = Routing.generate(this.choiceUrl, this.choiceUrlParams);

                    $.get(url, _.bind(function(data) {
                        this._updateChoices(data.results);
                        this.render();

                        MultiSelectFilter.prototype.show.apply(this, arguments);
                    }, this));
                } else {
                    MultiSelectFilter.prototype.show.apply(this, arguments);
                }
            },

            _updateChoices: function(results) {
                var choices = [];

                _.each(results, function(result) {
                    choices.push({ value: result.id, label: result.text });
                });
                choices.push({ value: 'empty', label: _.__('pim_datagrid.filters.common.empty') });
                choices.push({ value: 'not empty', label: _.__('pim_datagrid.filters.common.not_empty') });

                this.choices        = choices;
                this.choicesFetched = true;
            }
        });
    }
);
