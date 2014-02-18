define(
    ['jquery', 'underscore', 'oro/datafilter/number-filter', 'pim/tree/view', 'oro/mediator'],
    function ($, _, NumberFilter, TreeView, mediator) {
        'use strict';

        /**
         * Category filter
         *
         * @author    Filips Alpe <filips@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  oro/datafilter/category-filter
         * @class   oro.datafilter.CategoryFilter
         * @extends oro.datafilter.NumberFilter
         */
        return NumberFilter.extend({
            /**
             * Filter container selector
             *
             * @property {String}
             */
            container: '#tree',

            /**
             * @inheritDoc
             */
            emptyValue: {
                value: {
                    treeId:     0,
                    categoryId: 0
                },
                type: 0
            },

            /**
             * @inheritDoc
             */
            value: {},

            /**
             * @inheritDoc
             */
            events: {},

            /**
             * @inheritDoc
             */
            initialize: function(options) {
                mediator.once('datagrid_filters:rendered', this._init, this);

                NumberFilter.prototype.initialize.apply(this, arguments);
            },

            /**
             * @inheritDoc
             */
            render: function() {
            },

            /**
             * Initialize the tree
             *
             * @param {Object} options
             */
            _init: function(collection) {
                this.$el.remove();
                this.$el = $(this.container);

                var $filterChoices = $('#' + collection.inputName).find('#add-filter-select');
                $filterChoices.find('option[value="category"]').remove();
                $filterChoices.multiselect('refresh');

                this.value.value.categoryId = +this.value.value.categoryId;
                this.value.value.treeId     = +this.value.value.treeId;
                this.value.type             = +this.value.type;

                this.$el.on('tree.updated', _.bind(this._onTreeUpdated, this));
                TreeView.init(this.$el, this._getInitialState());

                mediator.on('grid_action_execute:' + collection.inputName + ':delete', function() {
                    TreeView.refresh();
                });
            },

            /**
             * Get the current tree state
             */
            _getTreeState: function() {
                var state = TreeView.getState();

                return {
                    value: {
                        treeId:     state.selectedTree,
                        categoryId: state.selectedNode
                    },
                    type: +state.includeSub
                };
            },

            /**
             * Get initial state for the tree
             */
            _getInitialState: function() {
                return {
                    selectedNode: +this.value.value.categoryId,
                    selectedTree: +this.value.value.treeId,
                    includeSub: !!this.value.type
                };
            },

            /**
             * Sync the tree state with the filter value
             */
            _updateState: function() {
                this.value = this._getTreeState();
            },

            /**
             * On tree updated
             */
            _onTreeUpdated: function (e, data) {
                if (!_.isEqual(this.value, this._getTreeState())) {
                    this._updateState();
                    this._triggerUpdate();
                }
            },

            /**
             * @inheritDoc
             */
            _triggerUpdate: function(newValue, oldValue) {
                this.trigger('update');
            },

            /**
             * @inheritDoc
             */
            isEmpty: function() {
                return _.isEqual(this.emptyValue, this._getTreeState());
            },

            /**
             * @inheritDoc
             */
            enable: function() {
                return this;
            },

            /**
             * @inheritDoc
             */
            disable: function() {
                return this;
            },

            /**
             * @inheritDoc
             */
            show: function() {
                return this;
            },

            /**
             * @inheritDoc
             */
            hide: function() {
                return this;
            },

            /**
             * @inheritDoc
             */
            reset: function() {
                TreeView.reset();
                NumberFilter.prototype.reset.apply(this, arguments);
            }
        });
    }
);
