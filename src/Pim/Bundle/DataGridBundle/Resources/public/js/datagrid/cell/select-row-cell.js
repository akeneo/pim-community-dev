/* global define */
define(['jquery', 'backbone', 'backgrid'],
function ($, Backbone, Backgrid) {
    "use strict";

    /**
     * Renders a checkbox for row selection.
     *
     * @export  oro/datagrid/select-row-cell
     * @class   oro.datagrid.SelectRowCell
     * @extends Backbone.View
     */
    return Backbone.View.extend({

        /** @property */
        className: "AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox select-row-cell",

        /** @property */
        tagName: "td",

        /** @property */
        events: {
            "change :checkbox": "onChange",
            "click": "enterEditMode"
        },

        /**
         * Initializer. If the underlying model triggers a `select` event, this cell
         * will change its checked value according to the event's `selected` value.
         *
         * @param {Object} options
         * @param {Backgrid.Column} options.column
         * @param {Backbone.Model} options.model
         */
        initialize: function (options) {
            //Backgrid.requireOptions(options, ["model", "column"]);

            this.column = options.column;
            if (!(this.column instanceof Backgrid.Column)) {
                this.column = new Backgrid.Column(this.column);
            }

            this.listenTo(this.model, "backgrid:select", function (model, checked) {
                this.$el.find(":checkbox").prop("checked", checked).change();
            });
        },

        /**
         * Focuses the checkbox.
         */
        enterEditMode: function (e) {
            var $checkbox = this.$el.find(":checkbox").focus();
            if ($checkbox[0] !== e.target) {
                $checkbox.prop("checked", !$checkbox.prop("checked")).change();
            }
            e.stopPropagation();
        },

        /**
         * Unfocuses the checkbox.
         */
        exitEditMode: function () {
            this.$el.find(":checkbox").blur();
        },

        /**
         * When the checkbox's value changes, this method will trigger a Backbone
         * `backgrid:selected` event with a reference of the model and the
         * checkbox's `checked` value.
         */
        onChange: function (e) {
            this.model.trigger("backgrid:selected", this.model, $(e.target).prop("checked"));
        },

        /**
         * Renders a checkbox in a table cell.
         */
        render: function () {
            // work around with trigger event to get current state of model (selected or not)
            var state = {selected: false};
            this.$el.empty().append('<input tabindex="-1" type="checkbox" />');
            this.model.trigger('backgrid:isSelected', this.model, state);
            if (state.selected) {
                this.$el.find(':checkbox').prop('checked', 'checked');
            }
            this.delegateEvents();
            return this;
        }
    });
});
