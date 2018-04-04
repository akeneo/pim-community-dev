/* global define */

/**
 * Contains mass-selection logic
 *  - watches models selection, keeps reference to selected
 *  - provides mass-selection actions
 *  - listening to models collection events,
 *      fills in 'obj' with proper data for
 *      `backgrid:isSelected` and `backgrid:getSelected`
 *
 * @export  oro/datagrid/select-all-header-cell
 * @class   oro.datagrid.SelectAllHeaderCell
 * @extends oro.datagrid.SelectRowCell
 */
define(
    [
        'jquery',
        'underscore',
        'backgrid',
        'oro/datagrid/select-row-cell'
    ], function (
        $,
        _,
        Backgrid,
        SelectRowCell
    ) {
    "use strict";

    return SelectRowCell.extend({
        /** @property */
        className: "AknGrid-headerCell AknGrid-headerCell--checkbox select-all-header-cell",

        /** @property */
        tagName: "th",

        events: {},

        /**
         * Initializer.
         * Subscribers on events listening
         *
         * @param {Object} options
         * @param {Backgrid.Column} options.column
         * @param {Backbone.Collection} options.collection
         */
        initialize: function (options) {
            //Backgrid.requireOptions(options, ["column", "collection"]);

            this.column = options.column;
            if (!(this.column instanceof Backgrid.Column)) {
                this.column = new Backgrid.Column(this.column);
            }

            this.initialState();
            this.listenTo(this.collection, {
                remove: this.removeModel,
                updateState: this.initialState,
                'backgrid:selected': this.selectModel,
                'backgrid:selectAll': this.selectAll,
                'backgrid:selectAllVisible': this.selectAllVisible,
                'backgrid:selectNone': this.selectNone,

                'backgrid:isSelected': _.bind(function (model, obj) {
                    if ($.isPlainObject(obj)) {
                        obj.selected = this.isSelectedModel(model);
                    }
                }, this),
                'backgrid:getSelected': _.bind(function (obj) {
                    if ($.isEmptyObject(obj)) {
                        obj.selected = _.keys(this.selectedModels);
                        obj.inset = this.inset;
                    }
                }, this)
            });
        },

        /**
         * Resets selection to initial conditions
         *  - clear selected models set
         *  - reset set type in-set/not-in-set
         */
        initialState: function () {
            this.selectedModels = {};
            this.inset = true;
        },

        /**
         * Gets selection state
         *
         * @returns {{selectedModels: *, inset: boolean}}
         */
        getSelectionState: function() {
            return {
                selectedModels: this.selectedModels,
                inset: this.inset
            };
        },

        /**
         * Checks if passed model have to be marked as selected
         *
         * @param {Backbone.Model} model
         * @returns {boolean}
         */
        isSelectedModel: function (model) {
            return this.inset === _.has(this.selectedModels, model.id || model.cid);
        },

        /**
         * Removes model from selected models set
         *
         * @param {Backbone.Model} model
         */
        removeModel: function (model) {
            delete this.selectedModels[model.id || model.cid];
        },

        /**
         * Adds/removes model to/from selected models set
         *
         * @param {Backbone.Model} model
         * @param {boolean} selected
         */
        selectModel: function (model, selected) {
            if (selected === this.inset) {
                this.selectedModels[model.id || model.cid] = model;
            } else {
                this.removeModel(model);
            }
        },

        /**
         * Performs selection of all possible models:
         *  - reset to initial state
         *  - change type of set type as not-inset
         *  - marks all models in collection as selected
         *  start to collect models which have to be excluded
         */
        selectAll: function () {
            this.initialState();
            this.inset = false;
            this._selectAll();
        },

        /**
         * Reset selection of all possible models:
         *  - reset to initial state
         *  - change type of set type as inset
         *  - marks all models in collection as not selected
         *  start to collect models which have to be included
         */
        selectNone: function () {
            this.initialState();
            this.inset = true;
            this._selectNone();
        },

        /**
         * Performs selection of all visible models:
         *  - if necessary reset to initial state
         *  - marks all models in collection as selected
         */
        selectAllVisible: function () {
            if (!this.inset) {
                this.initialState();
            }
            this._selectAllNotSelected();
        },

        /**
         * Marks all models in collection as selected
         *
         * @private
         */
        _selectAll: function () {
            this.collection.each(function (model) {
                model.trigger("backgrid:select", model, true);
            });
        },

        /**
         * Marks all non selected models in collection as selected
         *
         * @private
         */
        _selectAllNotSelected: function () {
            this.collection.each((model) => {
                if (!this.isSelectedModel(model)) {
                    model.trigger("backgrid:select", model, true);
                }
            });
        },

        /**
         * Marks all models in collection as not selected
         *
         * @private
         */
        _selectNone: function () {
            this.collection.each(function (model) {
                model.trigger("backgrid:select", model, false);
            });
        },

        /**
         *
         *
         * @returns {oro.datagrid.SelectAllHeaderCell}
         */
        render: function () {
            /* temp solution: start */
            // It's not clear for now, how mass selection will be designed,
            // thus implementation is done just to check functionality.
            // For future render method will depend on options or will be empty
            this.$el.empty().append('<div class="AknSeveralActionsButton AknSeveralActionsButton--small AknDropdown">\
                <button type="button" class="AknSeveralActionsButton-mainAction" data-select-all>'+_.__('pim_common.all')+'</button>\
                <button type="button" class="AknSeveralActionsButton-caretContainer dropdown-toggle" data-toggle="dropdown">\
                    <i class="AknCaret AknCaret--inverse"></i>\
                </button>\
                <ul class="AknSeveralActionsButton-menu AknDropdown-menu">\
                    <li><a class="AknDropdown-menuLink" href="#" data-select-all-visible>'+_.__('oro_datagrid.select.visible')+'</a></li>\
                    <li><a class="AknDropdown-menuLink" href="#" data-select-none>'+_.__('pim_common.none')+'</a></li>\
                </ul>\
            </div>');
            this.$el.find('[data-select-all]').on('click', _.bind(function (e) {
                this.collection.trigger('backgrid:selectAll');
                e.preventDefault();
            }, this));
            this.$el.find('[data-select-all-visible]').on('click', _.bind(function (e) {
                this.collection.trigger('backgrid:selectAllVisible');
                e.preventDefault();
            }, this));
            this.$el.find('[data-select-none]').on('click', _.bind(function (e) {
                this.collection.trigger('backgrid:selectNone');
                e.preventDefault();
            }, this));
            /* temp solution: end */
            return this;
        }
    });
});
