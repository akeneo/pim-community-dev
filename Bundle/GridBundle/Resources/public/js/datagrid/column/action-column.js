var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Column of grid that contains row actions
 *
 * @class   Oro.Datagrid.Action.Column
 * @extends Backgrid.Column
 */
Oro.Datagrid.Action.Column = Backgrid.Column.extend({

    /** @property {Object} */
    defaults:_.extend({}, Backgrid.Column.prototype.defaults, {
        name: '',
        label: '',
        editable: false,
        cell: Oro.Datagrid.Action.Cell,
        headerCell: Backgrid.HeaderCell.extend({
            className: 'action-column'
        }),
        sortable: false,
        actions: []
    }),

    /**
     * {@inheritDoc}
     */
    initialize: function (attrs) {
        attrs = attrs || {};
        if (!attrs.cell) {
            attrs.cell = this.defaults.cell;
        }
        if (!attrs.name) {
            attrs.name = this.defaults.name;
        }
        if (!attrs.actions || _.isEmpty(attrs.actions)) {
            this.set('renderable', false);
        }
        Backgrid.Column.prototype.initialize.apply(this, arguments);
    }
});
