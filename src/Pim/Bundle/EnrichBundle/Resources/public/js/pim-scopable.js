define(
    [
        'jquery',
        'backbone',
        'underscore',
        'oro/mediator',
        'wysiwyg',
        'pim/optionform',
        'pim/fileinput',
        'bootstrap',
        'bootstrap.bootstrapswitch',
        'jquery.select2'
    ],
    function ($, Backbone, _, mediator, wysiwyg, optionform, fileinput) {
        'use strict';
        /**
         * Allow expanding/collapsing scopable fields
         *
         * @author    Filips Alpe <filips@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         */
        var ScopableField = Backbone.View.extend({
            field:    null,
            rendered: false,
            isMetric: false,

            template: _.template(
                '<%= field.hiddenInput %>' +
                '<div class="control-group">' +
                    '<div class="controls input-prepend<%= isMetric ? " metric input-append" : "" %>">' +
                        '<label class="control-label add-on" for="<%= field.id %>" title="<%= field.scope %>">' +
                            '<%= field.scope[0].toUpperCase() %>' +
                        '</label>' +
                        '<div class="scopable-input">' +
                            '<%= field.input %>' +
                            '<div class="icons-container">' +
                                '<%= field.icons %>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            ),

            initialize: function () {
                var field = {};

                if (this.$el.find('.upload-zone').length) {
                    field.id = null;
                    field.input = this.$el.find('.upload-zone').get(0).outerHTML;
                } else if (this.$el.find('.switch').length) {
                    var $original = this.$el.find('.switch');
                    var $wrap = $original.clone().empty().removeClass('has-switch');
                    var $input = $original.find('input');

                    field.id = $input.attr('id');
                    $input.appendTo($wrap);

                    field.input = $wrap.get(0).outerHTML;
                } else if (this.$el.find('.control-label')) {
                    field.id = this.$el.find('.control-label').attr('for');

                    var $field = $('#' + field.id);

                    if ($field.hasClass('select2-input') || $field.hasClass('select2-focusser')) {
                        var id = $field.closest('.select2-container').attr('id');
                        if (/^s2id_.+/.test(id)) {
                            id = id.slice(5);
                            field.id = id;
                            $field = $('#' + id);
                        }
                        $field.select2('destroy');
                    }

                    field.input = $field.get(0).outerHTML;

                    _.each($field.siblings('input, select'), function (el) {
                        field.input += el.outerHTML;
                    });

                    if (this.$el.find('.controls.metric').length) {
                        this.isMetric = true;
                    }

                    if ($field.siblings('a.add-attribute-option').length) {
                        field.input += $field.siblings('a.add-attribute-option').get(0).outerHTML;
                    }

                    _.each($field.siblings('.validation-tooltip'), function (icon) {
                        $(icon).appendTo(this.$el.find('.icons-container'));
                    }.bind(this));
                }

                field.scope       = this.$el.data('scope');
                field.hiddenInput = this.$el.find('input[type="hidden"]').get(0).outerHTML;
                field.icons       = this.$el.find('.icons-container').html();

                this.field = field;
            },

            render: function () {
                if (!this.rendered) {
                    this.rendered = true;
                    this.$el.empty();
                    this.$el.append(
                        this.template({
                            field:    this.field,
                            isMetric: this.isMetric
                        })
                    );

                    this.$el.find('[data-toggle="tooltip"]').tooltip();
                    this.$el.find('.switch').bootstrapSwitch();
                    this.$el.find('select').select2();
                }

                return this;
            }
        });

        return Backbone.View.extend({
            label:        null,
            fieldViews:   [],
            fields:       [],
            expanded:     true,
            rendered:     false,
            expandIcon:   'icon-caret-right',
            collapseIcon: 'icon-caret-down',

            skipUIInit: false,

            template: _.template(
                '<%= label %>'
            ),

            initialize: function (opts) {
                var options = opts || {};
                this.fieldViews = [];
                this.fields     = [];
                this.expanded   = true;
                this.rendered   = false;

                this._reindexFields();

                _.each(this.fields, function ($field) {
                    this._addField($field);
                }.bind(this));

                this.label = this.$el.find('.control-label').first().get(0).outerHTML;

                this.render();

                if (_.has(options, 'initialScope')) {
                    this._changeDefault(options.initialScope);
                }

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

                var self = this;
                this.$el.closest('form').on('validate', function () {
                    if (self.$el.find('.validation-tooltip:hidden').length) {
                        self._expand();
                    }
                });
            },

            render: function () {
                if (!this.rendered) {
                    this.rendered = true;
                    this.$el.empty().addClass('control-group');
                    this.$el.append(
                        this.template({
                            label: this.label
                        })
                    );

                    if (this.fieldViews.length > 1) {
                        var $toggleIcon = $('<i>', { 'class': 'field-toggle ' + this.collapseIcon });
                        this.$el.find('label').removeAttr('for').prepend($toggleIcon);
                    }

                    _.each(this.fieldViews, function (fieldView) {
                        fieldView.render().$el.appendTo(this.$el);
                    }.bind(this));

                    this._collapse();

                    var $optionLink = this.$el.find('a.add-attribute-option');
                    if ($optionLink.length) {
                        optionform.init('#' + $optionLink.attr('id'));
                    }

                    mediator.trigger('scopablefield:rendered', this.$el);
                }

                return this;
            },

            _addField: function ($field) {
                this.fieldViews.push(new ScopableField({ el: $field }));

                return this;
            },

            _expand: function () {
                if (!this.expanded) {
                    this.expanded = true;

                    this._reindexFields();

                    var first = true;
                    _.each(this.fields, function (field) {
                        this._showField(field, first);
                        first = false;
                    }.bind(this));

                    this._initUI();
                    this.$el.find('i.field-toggle').removeClass(this.expandIcon).addClass(this.collapseIcon);
                    this.$el.removeClass('collapsed').addClass('expanded').trigger('expand');
                }

                return this;
            },

            _collapse: function () {
                if (this.expanded) {
                    this.expanded = false;

                    this._reindexFields();

                    var first = true;
                    _.each(this.fields, function (field) {
                        if (first) {
                            this._showField(field, first);
                            first = false;
                        } else {
                            this._hideField(field);
                        }
                    }.bind(this));

                    this._initUI();
                    this.$el.find('i.field-toggle').removeClass(this.collapseIcon).addClass(this.expandIcon);
                    this.$el.removeClass('expanded').addClass('collapsed').trigger('collapse');
                }

                return this;
            },

            _refreshFieldsDisplay: function () {
                _.each(this.fields, function ($field) {
                    if (this.expanded || $field.hasClass('first')) {
                        this._showField($field);
                    } else {
                        this._hideField($field);
                    }
                }.bind(this));
            },

            _toggle: function (e) {
                if (e) {
                    e.preventDefault();
                }

                return this.expanded ? this._collapse() : this._expand();
            },

            _changeDefault: function (scope) {
                this.skipUIInit = true;
                this._toggle();
                this._setFieldFirst(this.$el.find('[data-scope="' + scope + '"]:first'));
                this._refreshFieldsDisplay();
                this._initUI();

                return this;
            },

            _reindexFields: function () {
                this.fields = _.map(this.$el.find('[data-scope]'), function (field) {
                    return $(field);
                });

                if (this.$el.find('[data-scope]').length) {
                    _.first(this.fields).addClass('first');
                }
            },

            _setFieldFirst: function ($field) {
                this.$el.find('[data-scope]').removeClass('first');
                $field.addClass('first');

                var $target = this.$el.find('>label');
                if ($target.length) {
                    $field.insertAfter($target);
                } else {
                    $field.prependTo(this.$el);
                }
            },

            _showField: function (field, first) {
                var $icons = $(field).find('.icons-container i:not(".validation-tooltip")');

                if (first) {
                    $(field).addClass('first');
                    $icons.attr('style', 'display: inline !important');
                    this._setFieldFirst(field);
                } else {
                    $(field).removeClass('first');
                    $icons.attr('style', 'display: none !important');
                }

                $(field).show();
            },

            _hideField: function (field) {
                $(field).hide();
            },

            _initUI: function () {
                if (!this.skipUIInit) {
                    _.each(this.fields, function ($field) {
                        var $textarea = $field.find('textarea.wysiwyg');
                        if ($textarea.length) {
                            wysiwyg.init($textarea);
                        }

                        var $fileInput = $field.find('input[type=file][id]');
                        if ($fileInput.length) {
                            fileinput.init($fileInput.attr('id'));
                        }
                    });
                }

                return this;
            },

            events: {
                'click label i.field-toggle': '_toggle'
            }
        });
    }
);
