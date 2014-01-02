define(
    ['jquery', 'underscore', 'oro/translator', 'oro/datafilter/number-filter', 'routing', 'oro/mediator', 'oro/app',
    'jquery.jstree', 'jstree/jquery.jstree.tree_selector', 'jstree/jquery.jstree.nested_switch'],
    function ($, _, __, NumberFilter, Routing, mediator, app) {
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

            selectedNode: 0,
            dataLocale: null,

            /**
             * @inheritDoc
             */
            value: {},

            /**
             * @inheritDoc
             */
            events: {},

            /**
             * jsTree config
             *
             * @property {Object}
             */
            getTreeConfig: function() {
                return {
                    core: {
                        animation: 200
                    },
                    plugins: [
                        'tree_selector',
                        'nested_switch',
                        'themes',
                        'json_data',
                        'ui',
                        'crrm',
                        'types'
                    ],
                    nested_switch: {
                        state:    this.value.type,
                        label:    __('jstree.include_sub'),
                        callback: _.bind(function(state) {
                            this.value.type = +state;

                            this.$el.jstree('instance').data.tree_selector.ajax.url = this._getTreeUrl();
                            this.$el.jstree('refresh');
                            this.$el.trigger('after_tree_loaded.jstree');
                            this._triggerUpdate();
                        }, this)

                    },
                    tree_selector: {
                        ajax: {
                            'url': this._getTreeUrl()
                        },
                        auto_open_root: true,
                        node_label_field: 'label',
                        preselect_node_id: this.selectedNode
                    },
                    themes: {
                        dots: true,
                        icons: true
                    },
                    json_data: {
                        ajax: {
                            url: this._getChildrenUrl(),
                            data: _.bind(function (node) {
                                // the result is fed to the AJAX request `data` option
                                return {
                                    id: this._getNodeId(node),
                                    select_node_id: this.selectedNode,
                                    with_products_count: 1,
                                    include_sub: this.value.type
                                };
                            }, this)
                        }
                    },
                    types: {
                        max_depth: -2,
                        max_children: -2,
                        valid_children: ['folder'],
                        types: {
                            'default': {
                                valid_children: 'folder'
                            }
                        }
                    },
                    ui: {
                        select_limit: 1,
                        select_multiple_modifier: false
                    }
                };
            },

            /**
             * @inheritDoc
             */
            initialize: function(options) {
                mediator.on('datagrid_filters:rendered', this._init, this);

                NumberFilter.prototype.initialize.apply(this, arguments);
            },

            render: function() {
            },

            /**
             * Initialize the tree
             *
             * @param {Object} options
             */
            _init: function() {
                this.$el.remove();
                this.$el = $(this.container);
                this.dataLocale = this.$el.attr('data-datalocale');

                var treeId = +this.value.value.treeId,
                    categoryId = +this.value.value.categoryId;

                this.selectedNode = categoryId !== 0 ? categoryId : treeId;

                this.$el.jstree(this.getTreeConfig())
                    .on('trees_loaded.jstree', this._onTreesLoaded)
                    .on('after_tree_loaded.jstree', _.bind(this._afterTreeLoaded, this))
                    .on('after_open.jstree correct_state.jstree', _.bind(this._afterOpenNode, this))
                    .on('select_node.jstree', _.bind(this._onSelectNode, this));
            },

            _onTreesLoaded: function(event, tree_select_id) {
                $('#' + tree_select_id).select2({ width: '100%' });
            },

            _afterTreeLoaded: function (e, root_node_id) {
                if (!$('#node_0').length) {
                    this.$el.jstree('create', -1, 'last', {
                        attr: { 'class': 'jstree-unclassified', id: 'node_0' },
                        data: { title: __('jstree.all') }
                    }, null, true);
                    if (0 === this.selectedNode) {
                        this.$el.jstree('select_node', '#node_0');
                    }
                }
            },

            _afterOpenNode: function (e, data) {
                var $node = $(data.args[0]);

                if ($node.attr('rel') === 'folder' && !$('#node_-1').length) {
                    this.$el.jstree('create', '#' + $node.attr('id'), 'last', {
                        attr: { 'class': 'jstree-unclassified', id: 'node_-1' },
                        data: { title: __('jstree.unclassified') }
                    }, null, true);
                    if (-1 === this.selectedNode) {
                        this.$el.jstree('select_node', '#node_-1');
                    }
                }
            },

            _getNodeId: function (node) {
                var nodeId = (node && node.attr('id')) ? node.attr('id').replace('node_', '') : '';
                return +nodeId;
            },

            _onSelectNode: function (e, data) {
                var $node = $(data.args).parent();
                var nodeId = this._getNodeId($node);
                this.selectedNode = nodeId;

                if ($node.attr('rel') === 'folder') {
                    this.value.value.treeId     = nodeId;
                    this.value.value.categoryId = 0;
                } else {
                    this.value.value.categoryId = nodeId;
                    this.value.value.treeId     = this._getNodeId(this.$el.find('li').first());
                }
                this._triggerUpdate();
            },

            _getTreeUrl: function() {
                return Routing.generate(
                    'pim_catalog_categorytree_listtree',
                    {
                        _format: 'json',
                        dataLocale: this.dataLocale,
                        select_node_id: this.selectedNode,
                        include_sub: this.value.type
                    }
                );
            },

            _getChildrenUrl: function() {
                return Routing.generate(
                    'pim_catalog_categorytree_children',
                    {
                        _format: 'json',
                        dataLocale: this.dataLocale
                    }
                );
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
                return false;
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
            }
        });
    }
);
