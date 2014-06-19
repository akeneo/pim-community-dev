define(
    ['jquery', 'underscore', 'oro/translator', 'routing', 'jquery.jstree', 'jstree/jquery.jstree.tree_selector', 'jstree/nested_switch'],
    function ($, _, __, Routing) {
        'use strict';

        return function (roleId, appendField, removeField, dataLocale) {
            var $list = $('#trees-list').find('ul');
            var $trees = $('#trees');
            var selectedTree = null;
            var self = this;

            this.config = {
                core: {
                    animation: 200,
                    html_titles: true
                },
                plugins: [
                    'themes',
                    'json_data',
                    'ui',
                    'types',
                    'checkbox'
                ],
                checkbox: {
                    two_state: true,
                    override_ui: true
                },
                themes: {
                    dots: true,
                    icons: true
                },
                json_data: {
                    ajax: {
                        url: function (node) {
                            if ((!node || (node === -1))) {
                                // First load of the tree: get the checked categories
                                return Routing.generate('pimee_security_role_listcategories', { id: roleId, tree_id: selectedTree, _format: 'json', dataLocale: dataLocale });
                            }

                            return Routing.generate('pim_enrich_categorytree_children', { _format: 'json', dataLocale: dataLocale });
                        },
                        data: function (node) {
                            var data = {};

                            if (node && node !== -1 && node.attr) {
                                data.id = node.attr('id').replace('node_', '');
                            } else {
                                data.id = selectedTree;
                                data.include_parent = 'true';
                            }

                            return data;
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
                }
            };

            this.initTree = function (treeId) {
                var $tree = $('#tree-' + treeId);
                $tree.jstree(self.config);

                $tree.bind('check_node.jstree', function (e, d) {
                    if (d.inst.get_checked()) {
                        var id = d.rslt.obj[0].id.replace('node_', '');
                        var removed = $(removeField).val();
                        removed = removed.length > 0 ? removed.split(',') : [];
                        var appended = $(appendField).val();
                        appended = appended.length > 0 ? appended.split(',') : [];

                        if (-1 !== _.indexOf(removed, id)) {
                            removed = _.uniq(_.without(removed, id)).join(',');
                            $(removeField).val(removed).trigger('change');
                        } else {
                            appended.push(id);
                            appended = _.uniq(appended).join(',');
                            $(appendField).val(appended).trigger('change');
                        }
                    }
                });

                $tree.bind('uncheck_node.jstree', function (e, d) {
                    if (d.inst.get_checked()) {
                        var id = d.rslt.obj[0].id.replace('node_', '');
                        var removed = $(removeField).val();
                        removed = removed.length > 0 ? removed.split(',') : [];
                        var appended = $(appendField).val();
                        appended = appended.length > 0 ? appended.split(',') : [];

                        if (-1 !== _.indexOf(appended, id)) {
                            appended = _.uniq(_.without(appended, id)).join(',');
                            $(appendField).val(appended).trigger('change');
                        } else {
                            removed.push(id);
                            removed = _.uniq(removed).join(',');
                            $(removeField).val(removed).trigger('change');
                        }
                    }
                });
            };

            this.switchTree = function (treeId) {
                selectedTree = treeId;
                var $tree = $('#tree-' + treeId);

                $trees.find('> div').hide();
                $list.find('li').removeClass('active');
                $('#tree-link-' + treeId).parent().addClass('active');

                $('.tree[data-tree-id=' + treeId + ']').show();
                $tree.show();
                $('#tree-link-' + treeId).trigger('shown');

                // If empty, load the associated jstree
                if ($tree.children('ul').size() === 0) {
                    self.initTree(treeId);
                }
            };

            $('#trees-list').on('click', 'a', function () {
                self.switchTree(this.id.replace('tree-link-', ''));
            });

            var treeUrl = Routing.generate(
                'pim_enrich_categorytree_listtree',
                {
                    _format: 'json',
                    dataLocale: dataLocale,
                    'with_products_count': 0
                }
            );
            $.get(treeUrl, function(trees) {
                _.each(trees, function(tree) {
                    $list.append($('<li>', {'class': tree.selected ? 'active' : ''}).html(
                        $('<a>', {'href': 'javascript:void(0);', 'data-toggle': 'tab', id: 'tree-link-' + tree.id }).text(tree.label))
                    );
                    $trees.append(
                        $('<div>', {'class': 'tree buffer-small-left', 'data-tree-id': tree.id }).append(
                            $('<div>', { id: 'tree-' + tree.id })
                        )
                    );
                });
                self.switchTree(trees[0].id);
            });
        };
    }
);
