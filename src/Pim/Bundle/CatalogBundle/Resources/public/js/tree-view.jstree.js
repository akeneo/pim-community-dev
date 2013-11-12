define(
    ['jquery', 'underscore', 'routing', 'oro/registry', 'oro/translator', 'jquery.jstree', 'jstree/jquery.jstree.tree_selector', 'jstree/jquery.jstree.nested_switch'],
    function ($, _, Routing, Registry, __) {
        'use strict';

        return function (elementId) {
            var $el = $(elementId);
            if (!$el || !$el.length || !_.isObject($el)) {
                throw new Error('Unable to instantiate tree on this element');
            }
            var self               = this,
                dataLocale         = $el.attr('data-datalocale'),
                selectedNode       = $el.attr('data-node-id') || -1,
                selectedTree       = $el.attr('data-tree-id') || -1,
                includeChildren    = $el.attr('data-include-sub') || false,
                selectedNodeOrTree = selectedNode in [0, -1] ? selectedTree : selectedNode;

            var getTreeUrl = function() {
                return Routing.generate('pim_catalog_categorytree_listtree', { _format: 'json', dataLocale: dataLocale, select_node_id: selectedNodeOrTree, include_sub: +includeChildren });
            }

            this.config = {
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
                    state:    includeChildren,
                    label:    __('jstree.include_sub'),
                    callback: function(state) {
                        includeChildren = state;

                        $el.jstree('instance').data.tree_selector.ajax.url = getTreeUrl();
                        $el.jstree('refresh');
                    }
                },
                tree_selector: {
                    ajax: {
                        'url': getTreeUrl()
                    },
                    auto_open_root: true,
                    node_label_field: 'label',
                    preselect_node_id: selectedNode
                },
                themes: {
                    dots: true,
                    icons: true
                },
                json_data: {
                    ajax: {
                        url: Routing.generate('pim_catalog_categorytree_children', { _format: 'json', dataLocale: dataLocale }),
                        data: function (node) {
                            // the result is fed to the AJAX request `data` option
                            var id = (node && node !== -1) ? node.attr('id').replace('node_', '') : -1;

                            return {
                                id: id,
                                select_node_id: selectedNode,
                                with_products_count: 1,
                                include_sub: +includeChildren
                            };
                        }
                    }
                },
                types: {
                    max_depth: -2,
                    max_children: -2,
                    valid_children: [ 'folder' ],
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

            function updateGrid(treeId, categoryId, includeSub) {
                var collection = Registry.getElement('datagrid', 'products').collection;
                if (collection.setCategory(treeId, categoryId, includeSub)) {
                    $('.grid-toolbar .icon-refresh').click();
                }
            }

            this.init = function () {
                $el.jstree(self.config).on('trees_loaded.jstree', function (event, tree_select_id) {
                    if (event.namespace === 'jstree') {
                        $('#' + tree_select_id).select2({ width: '100%' });
                    }
                }).on('after_tree_loaded.jstree', function (e, root_node_id) {
                    $(document).one('ajaxStop', function () {
                        if (!$('#node_').length) {
                            $el.jstree('create', -1, 'last', {
                                attr: { 'class': 'jstree-unclassified', id: 'node_' },
                                data: { title: _.__('jstree.all') }
                            }, null, true);
                        }
                        if (-1 === selectedNode) {
                            $el.jstree('select_node', '#node_');
                        }

                        $el.jstree('create', '#node_' + root_node_id, 'last', {
                            attr: { 'class': 'jstree-unclassified', id: 'node_0' },
                            data: { title: _.__('jstree.unclassified') }
                        }, null, true);
                        if (0 === +selectedNode) {
                            $el.jstree('select_node', '#node_0');
                        }
                    });
                }).on('select_node.jstree', function () {
                    function getNodeId(node) {
                        var nodeId = (node && node.attr('id')) ? node.attr('id').replace('node_', '') : '';
                        return nodeId !== '' ? nodeId : -1;
                    }
                    var nodeId = getNodeId($.jstree._focused().get_selected()),
                        treeId = getNodeId($('#tree').find('li').first());
                    if (nodeId !== '') {
                        selectedNode = nodeId;
                    }
                    updateGrid(treeId, nodeId, includeChildren);
                });
            };

            this.init();
        };
    }
);
