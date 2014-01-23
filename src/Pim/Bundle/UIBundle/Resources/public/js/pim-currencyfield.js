define(
    ['jquery', 'backbone', 'underscore', 'oro/mediator', 'bootstrap'],
    function ($, Backbone, _, mediator) {
        'use strict';
        /**
         * Allow expanding/collapsing currency fields
         *
         * @author    Filips Alpe <filips@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         */
        return Backbone.View.extend({
            fieldSelector: '.currency-field[data-metadata]',
            metadata:      null,

            template: _.template(
                '<div class="currency-field" style="display:inline-block;">' +
                    '<table class="table table-condensed table-bordered<%= scopable ? " table-hover" : "" %>">' +
                        '<thead>' +
                            '<tr>' +
                                '<%= scopable ? "<th></th>" : "" %>' +
                                '<% _.each(currencies, function(currency) { %>' +
                                    '<th><%= currency %></th>' +
                                '<% }); %>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>' +
                            '<% _.each(data, function(item) { %>' +
                                '<% _.each(currencies, function(currency, index) { %>' +
                                    '<% if (item.label === currency) { %>' +
                                        '<% if (index === 0) { %>' +
                                            '<tr><%= scopable ? "<td>" + item.scope + "</td>" : "" %>' +
                                        '<% } %>' +
                                        '<td>' +
                                            '<input type="text" class="input-small" id="<%= item.value.fieldId %> "' +
                                                'name="<%= item.value.fieldName %>" value="<%= item.value.data %>"' +
                                                '<%= item.value.disabled ? " disabled" : "" %> >' +
                                            '<input type="hidden" id="<%= item.currency.fieldId %>" ' +
                                                'name="<%= item.currency.fieldName %>" value="<%= item.currency.data %>"' +
                                                '<%= item.currency.disabled ? " disabled" : "" %> >' +
                                        '</td>' +
                                        '<% if (index + 1 === currencies.length) { %>' +
                                            '</tr>' +
                                        '<% } %>' +
                                    '<% } %>' +
                                '<% }); %>' +
                            '<% }); %>' +
                        '</tbody>' +
                    '</table>' +
                '</div>'
            ),

            initialize: function () {
                this._extractMetadata();
                this.render();
            },

            _extractMetadata: function () {
                var data = [];
                var scopable = false;
                this.$el.find(this.fieldSelector).each(function() {
                    var metadata = $(this).data('metadata');
                    var scope = $(this).parent().parent().parent().data('scope');
                    if (scope) {
                        scopable = true;
                        metadata.scope = scope;
                    }
                    data.push(metadata);
                });

                this.metadata = {
                    currencies: _.uniq(_.pluck(data, 'label')),
                    data:       data,
                    scopable:   scopable
                };
            },

            render: function () {
                this.$el.find('>.control-group:not(:first)').remove();
                this.$el.find('.control-group.hide').removeClass('hide');

                var $target = this.$el.find('.controls').eq(0);
                $target.find('.currency-field').remove();
                $target.prepend(
                    this.template({
                        currencies: this.metadata.currencies,
                        data:       this.metadata.data,
                        scopable:   this.metadata.scopable
                    })
                );

                return this;
            }
        });
    }
);
