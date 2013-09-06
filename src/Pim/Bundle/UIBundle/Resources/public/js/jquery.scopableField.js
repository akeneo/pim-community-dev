/**
 * Allow expanding/collapsing scopable fields
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
var Pim = Pim || {};
Pim.View = Pim.View || {};

Pim.View.ScopableField = Backbone.View.extend({
    field:    null,
    rendered: false,

    template: _.template(
        '<%= field.hiddenInput %>' +
        '<div class="control-group">' +
            '<div class="controls input-prepend">' +
                '<label class="control-label add-on" for="<%= field.id %>" style="height: <%= field.height - 10 %>px;">' +
                    '<span class="field-toggle">' +
                        '<i class="fa-icon-caret-down"></i>' +
                    '</span>' +
                    '<%= field.scope %>' +
                '</label>' +
                '<div class="scopable-input" style="display: inline-block; height: <%= field.height %>px;">' +
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
        } else if (this.$el.find('.control-label')) {
            field.id = this.$el.find('.control-label').attr('for');
            field.input = $('#' + field.id).get(0).outerHTML;
        }

        field.scope       = this.$el.data('scope');
        field.hiddenInput = this.$el.find('input[type="hidden"]').get(0).outerHTML;
        field.icons       = this.$el.find('.icons-container').html();
        field.height      = this.$el.actual('height');

        this.field = field;
    },

    render: function () {
        if (!this.rendered) {
            this.rendered = true;
            this.$el.empty();
            this.$el.append(
                this.template({
                    field: this.field
                })
            );

            this.$el.find('[data-toggle="tooltip"]').tooltip();
        }

        return this;
    }
});

Pim.View.Scopable = Backbone.View.extend({
    label:        null,
    fieldViews:   [],
    fields:       [],
    expanded:     true,
    rendered:     false,
    expandIcon:   'fa-icon-caret-right',
    collapseIcon: 'fa-icon-caret-down',

    template: _.template(
        '<label class="control-label"><%= label %></label>'
    ),

    initialize: function () {
        this.fieldViews = [];
        this.fields     = [];
        this.expanded   = true;
        this.rendered   = false;

        this._reindexFields();

        _.each(this.fields, function (field) {
            this._addField(field);
        }, this);

        this.label = this.$el.find('.control-label').first().html();

        this.render();

        Oro.Events.on('scopablefield:changescope', function (scope) {
            this._changeDefault(scope);
        }, this);

        Oro.Events.on('scopablefield:collapse', function () {
            this._collapse();
        }, this);

        Oro.Events.on('scopablefield:expand', function () {
            this._expand();
        }, this);

        this.$el.closest('form').on('validate', function () {
            if (this.$el.find('.validation-tooltip:hidden').length) {
                this._expand();
            }
        }, this);
    },

    render: function () {
        if (!this.rendered) {
            this.rendered = true;
            this.$el.empty();
            this.$el.append(
                this.template({
                    label: this.label
                })
            );

            _.each(this.fieldViews, function (fieldView) {
                fieldView.render().$el.appendTo(this.$el);
            }, this);

            this._collapse();
        }

        return this;
    },

    _addField: function (field) {
        this.fieldViews.push(new Pim.View.ScopableField({ el: field }));

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
            }, this);

            this.$el.removeClass('collapsed').addClass('expanded');
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
            }, this);

            this.$el.removeClass('expanded').addClass('collapsed');
        }

        return this;
    },

    _toggle: function () {
        return this.expanded ? this._collapse() : this._expand();
    },

    _changeDefault: function (scope) {
        _.each(this.fields, function (field) {
            if ($(field).data('scope') === scope) {
                this._setFieldFirst(field);
            }
        }, this);

        this._toggle();
        this._toggle();

        return this;
    },

    _reindexFields: function () {
        this.fields = this.$el.find('[data-scope]');
    },

    _setFieldFirst: function (field) {
        var $field = $(field);
        $field.insertAfter(this.$el.find('>label'));
        $field.find('.field-toggle').removeClass('hide');

        if (this.expanded) {
            $field.find('.field-toggle i').removeClass(this.expandIcon).addClass(this.collapseIcon);
        } else {
            $field.find('.field-toggle i').removeClass(this.collapseIcon).addClass(this.expandIcon);
        }
    },

    _showField: function (field, first) {
        if (first) {
            this._setFieldFirst(field);
        }
        $(field).show();
    },

    _hideField: function (field) {
        $(field).hide().find('.field-toggle').addClass('hide');
    },

    events: {
        'click label span.field-toggle' : '_toggle'
    }
});
