/* global define */
define(['underscore', 'backgrid', 'oro/datagrid/moment-formatter', 'backgrid/moment'],
function(_, Backgrid, MomentFormatter) {
    'use strict';

    /**
     * Datetime column cell. Added missing behavior.
     *
     * @export  oro/datagrid/moment-cell
     * @class   oro.datagrid.MomentCell
     * @extends Backgrid.Extension.MomentCell
     */
    return Backgrid.Extension.MomentCell.extend({

        /** @property {Backgrid.CellFormatter} */
        formatter: MomentFormatter,

        /**
         * NOTE: overridden to use oro/momment formatter prototype in initialization
         * Initializer. Accept Backgrid.Extension.MomentFormatter.options and
         * Backgrid.Cell.initialize required parameters.
         */
        initialize: function (options) {

            Backgrid.Cell.prototype.initialize.apply(this, arguments);

            var formatterDefaults = MomentFormatter.prototype.defaults;
            var formatterDefaultKeys = _.keys(formatterDefaults);
            var classAttrs = _.pick(this, formatterDefaultKeys);
            var formatterOptions = _.pick(options, formatterDefaultKeys);

            this.formatter = new this.formatter(_.extend({}, formatterDefaults, classAttrs, formatterOptions));

            this.editor = this.editor.extend({
                attributes: _.extend({}, this.editor.prototype.attributes || this.editor.attributes || {}, {
                    placeholder: this.formatter.displayFormat
                })
            });
        },

        /**
         * @inheritDoc
         */
        enterEditMode: function (e) {
            if (this.column.get("editable")) {
                e.stopPropagation();
            }
            return Backgrid.Extension.MomentCell.prototype.enterEditMode.apply(this, arguments);
        }
    });
});
