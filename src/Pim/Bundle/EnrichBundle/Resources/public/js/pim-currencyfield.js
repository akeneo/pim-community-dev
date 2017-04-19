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
            fieldSelector:   '.currency-field[data-metadata]',
            expandIcon:      'icon-caret-right',
            collapseIcon:    'icon-caret-down',
            first:           true,
            expanded:        true,
            currencies:      null,
            scopable:        false,
            inputClass:      'input-small',
            smallInputClass: 'input-mini',
            inputThreshold:  3,

            currencyTemplate: _.template(
                '<span class="currency-header<%= small ? " small" : "" %>">' +
                    '<% _.each(currencies, function (currency) { %>' +
                        '<span class="currency-label"><%= currency %></span>' +
                    '<% }); %>' +
                '</span>'
            ),

            template: _.template(
                '<% _.each(data, function (item) { %>' +
                    '<% _.each(currencies, function (currency, index) { %>' +
                        '<% if (item.label === currency) { %>' +
                            '<% if (scopable && index === 0) { %>' +
                                '<label class="control-label add-on" title="<%= item.scope %>">' +
                                    '<%= item.scope[0].toUpperCase() %>' +
                                '</label>' +
                                '<div class="scopable-input">' +
                            '<% } %>' +
                            '<input type="hidden" id="<%= item.currency.fieldId %>" ' +
                                'name="<%= item.currency.fieldName %>" value="<%= item.currency.data %>"' +
                                '<%= item.currency.disabled ? " disabled" : "" %> >' +
                            '<input type="text" class="<%= inputClass %>" id="<%= item.value.fieldId %>"' +
                                'name="<%= item.value.fieldName %>" value="<%= item.value.data %>"' +
                                '<% if (!scopable && index === 0) { %>' +
                                    ' style="border-top-left-radius:3px;border-bottom-left-radius:3px;"' +
                                '<% } %>' +
                                '<%= item.value.disabled ? " disabled" : "" %> >' +
                            '<% if (scopable && index + 1 === currencies.length) { %>' +
                                '</div>' +
                            '<% } %>' +
                        '<% } %>' +
                    '<% }); %>' +
                '<% }); %>'
            ),

            events: {
                'click label i.field-toggle': '_toggle'
            },

            initialize: function () {
                this._extractMetadata();
                this.render();

                if (this.scopable) {
                    mediator.on('scopablefield:changescope', function (scope) {
                        this._changeDefault(scope);
                    }.bind(this));

                    mediator.on('scopablefield:collapse', function (id) {
                        if (!id || this.$el.find('#' + id).length) {
                            this._collapse();
                        }
                    }.bind(this));

                    mediator.on('scopablefield:expand', function (id) {
                        if (!id || this.$el.find('#' + id).length) {
                            this._expand();
                        }
                    }.bind(this));
                }
            },

            _extractMetadata: function () {
                this.scopable = this.$el.hasClass('scopable');
                var currencies = [];

                this.$el.find(this.fieldSelector).each(function () {
                    var metadata = $(this).data('metadata');
                    currencies.push(metadata.label);
                });

                this.currencies = _.uniq(currencies);
            },

            _renderTarget: function (index, target) {
                var $target = $(target);
                var data = [];

                var extractScope = this.scopable;

                $target.find(this.fieldSelector).each(function () {
                    var metadata = $(this).data('metadata');
                    if (extractScope) {
                        metadata.scope = $(this).parent().parent().parent().data('scope');
                    }
                    data.push(metadata);
                });

                $target.empty();
                $target.prepend(
                    this.template({
                        currencies:   this.currencies,
                        data:         data,
                        scopable:     this.scopable,
                        first:        this.first,
                        collapseIcon: this.collapseIcon,
                        inputClass:   this.currencies.length > this.inputThreshold ?
                                        this.smallInputClass : this.inputClass
                    })
                );

                if (this.first) {
                    $target.parent().parent().addClass('first');
                    this.first = false;
                }
            },

            render: function () {
                this.$el.addClass('control-group').find('.control-group.hide').removeClass('hide');

                var $label = this.$el.find('label.control-label:first').prependTo(this.$el);
                this.$el.find('label.control-label:not(:first)').remove();

                var $fields = this.$el.find('div[data-scope]');

                if (this.scopable && $fields.length > 1) {
                    var $toggleIcon = $('<i>', { 'class': 'field-toggle ' + this.collapseIcon });
                    $label.prepend($toggleIcon);
                }

                $fields.each(function () {
                    var $parent = $(this).parent();
                    $(this).insertBefore($parent);
                    $parent.remove();
                });

                if (this.scopable) {
                    this.$el.find('div.controls').addClass('input-prepend');
                }

                var $header = $(this.currencyTemplate({
                    currencies: this.currencies,
                    scopable:   this.scopable,
                    small:      this.currencies.length > this.inputThreshold
                }));
                $header.insertAfter($label);
                var $iconsContainer = this.$el.find('.icons-container:first');
                $iconsContainer.insertAfter($header);

                _.each(this.$el.find('.validation-tooltip'), function (tooltip) {
                    $(tooltip).appendTo($iconsContainer);
                });

                var $targets = this.$el.find('div.controls');

                $targets.each(this._renderTarget.bind(this));

                if (this.scopable) {
                    $iconsContainer.appendTo(this.$el.find('div.first .scopable-input'));
                    this._collapse();
                    mediator.trigger('scopablefield:rendered', this.$el);
                } else {
                    $iconsContainer.appendTo(this.$el.find('.controls'));
                }

                return this;
            },

            _expand: function () {
                if (!this.expanded) {
                    this.expanded = true;

                    this.$el.find('div[data-scope]').removeClass('hide');
                    this.$el.find('i.field-toggle').removeClass(this.expandIcon).addClass(this.collapseIcon);
                    this.$el.removeClass('collapsed').addClass('expanded').trigger('expand');
                }

                return this;
            },

            _collapse: function () {
                if (this.expanded) {
                    this.expanded = false;

                    this.$el.find('div[data-scope]:not(:first)').addClass('hide');
                    this.$el.find('i.field-toggle').removeClass(this.collapseIcon).addClass(this.expandIcon);
                    this.$el.removeClass('expanded').addClass('collapsed').trigger('collapse');
                }

                return this;
            },

            _toggle: function (e) {
                if (e) {
                    e.preventDefault();
                }

                return this.expanded ? this._collapse() : this._expand();
            },

            _changeDefault: function (scope) {
                var $fields = this.$el.find('>div[data-scope]');
                this.$el.find('.first').removeClass('first');
                var $firstField = $fields.filter('[data-scope="' + scope + '"]');

                $firstField.addClass('first').insertBefore($fields.eq(0));

                if (this.scopable) {
                    var $iconsContainer = this.$el.find('.icons-container:first');
                    $iconsContainer.appendTo(this.$el.find('div.first .scopable-input'));
                }

                this._toggle();
                this._toggle();

                return this;
            }
        });
    }
);
