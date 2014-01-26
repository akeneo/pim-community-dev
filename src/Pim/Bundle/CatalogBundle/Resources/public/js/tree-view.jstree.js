define(
    ['jquery', 'underscore', 'oro/translator', 'routing', 'jquery.jstree', 'jstree/jquery.jstree.tree_selector', 'jstree/jquery.jstree.nested_switch'],
    function ($, _, __, Routing) {
        'use strict';

        var selectedNode = 0,
            selectedTree = 0,
            activeNode   = 0,
            includeSub   = false,
            dataLocale   = null,
            $el          = null,

            triggerUpdate = function() {
                $el.trigger('tree.updated');
            },

            getTreeUrl = function() {
                return Routing.generate(
                    'pim_enrich_categorytree_listtree',
                    {
                        _format:        'json',
                        dataLocale:     dataLocale,
                        select_node_id: activeNode,
                        include_sub:    +includeSub
                    }
                );
            },

            getChildrenUrl = function() {
                return Routing.generate(
                    'pim_enrich_categorytree_children',
                    {
                        _format:    'json',
                        dataLocale: dataLocale
                    }
                );
            },

            selectNode = function(nodeId) {
                $el.jstree('select_node', '#node_'+nodeId);
            },

            createNode = function(id, target, title) {
                var targetId = target !== null ? '#' + target : -1;
                $el.jstree('create', targetId, 'last', {
                    attr: { 'class': 'jstree-unclassified', id: 'node_'+id },
                    data: { title: __(title) }
                }, null, true);

                if (id === activeNode) {
                    selectNode(id);
                }
            },

            getNodeId = function (node) {
                var nodeId = (node && node.attr('id')) ? node.attr('id').replace('node_', '') : '';
                return +nodeId;
            },

            getTreeConfig = function() {
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
                        state:    includeSub,
                        label:    __('jstree.include_sub'),
                        callback: function(state) {
                            includeSub = state;

                            $el.jstree('instance').data.tree_selector.ajax.url = getTreeUrl();
                            $el.jstree('refresh');
                            $el.trigger('after_tree_loaded.jstree');
                            triggerUpdate();
                        }

                    },
                    tree_selector: {
                        ajax: {
                            'url': getTreeUrl()
                        },
                        auto_open_root: true,
                        node_label_field: 'label',
                        preselect_node_id: activeNode
                    },
                    themes: {
                        dots: true,
                        icons: true
                    },
                    json_data: {
                        ajax: {
                            url: getChildrenUrl(),
                            data: function (node) {
                                // the result is fed to the AJAX request `data` option
                                return {
                                    id: getNodeId(node),
                                    select_node_id: activeNode,
                                    with_products_count: 1,
                                    include_sub: +includeSub
                                };
                            }
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

            initTree = function() {
                $el.jstree(getTreeConfig())
                    .on('trees_loaded.jstree', onTreesLoaded)
                    .on('after_tree_loaded.jstree', afterTreeLoaded)
                    .on('after_open.jstree correct_state.jstree', afterOpenNode)
                    .on('select_node.jstree', onSelectNode);
            },

            onTreesLoaded = function(event, tree_select_id) {
                $('#' + tree_select_id).select2({ width: '100%' });
            },

            afterTreeLoaded = function (e, root_node_id) {
                if (root_node_id && selectedTree !== +root_node_id) {
                    selectedTree = +root_node_id;
                    activeNode   = 0;
                }
                if (!$('#node_0').length) {
                    createNode(0, null, 'jstree.all');
                }
            },

            afterOpenNode = function (e, data) {
                var $node = $(data.args[0]);

                if ($node.attr('rel') === 'folder' && !$('#node_-1').length) {
                    createNode(-1, $node.attr('id'), 'jstree.unclassified');
                }
            },

            onSelectNode = function (e, data) {
                if (data.args.length === 1) {
                    // Return if the select was not user triggered
                    return;
                }
                var $node = $(data.args).parent();
                var nodeId = getNodeId($node);
                activeNode = nodeId;

                if ($node.attr('rel') === 'folder') {
                    selectedNode = 0;
                    selectedTree = nodeId;
                } else {
                    selectedNode = nodeId;
                    selectedTree = getNodeId($el.find('li').first());
                }
                triggerUpdate();
            };

        return {
            init: function($element, state) {
                if (!$element || !$element.length || !_.isObject($element)) {
                    throw new Error('Unable to instantiate tree on this element');
                }

                $el          = $element;
                dataLocale   = $el.attr('data-datalocale');
                selectedNode = _.has(state, 'selectedNode') ? state.selectedNode : selectedNode;
                selectedTree = _.has(state, 'selectedTree') ? state.selectedTree : selectedTree;
                includeSub   = _.has(state, 'includeSub')   ? state.includeSub   : includeSub;
                activeNode   = selectedNode;

                initTree();
            },

            getState: function() {
                return {
                    selectedNode: selectedNode,
                    selectedTree: activeNode === 0 ? 0 : selectedTree,
                    includeSub:   includeSub
                };
            },

            refresh: function() {
                initTree();
            }
        };
    }
);
