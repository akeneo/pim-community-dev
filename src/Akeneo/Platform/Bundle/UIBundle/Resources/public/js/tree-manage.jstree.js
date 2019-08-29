define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'oro/loading-mask',
        'oro/error',
        'pim/ui',
        'jquery.jstree',
        'jstree/jquery.jstree.tree_selector'
    ],
    function ($, _, Backbone, Routing, LoadingMask, OroError, UI) {
        'use strict';

        return function (elementId, prefixRoute) {
            var $el = $(elementId);
            if (!$el || !$el.length || !_.isObject($el)) {
                throw new Error('Unable to instantiate tree on this element');
            }
            var selectedNode       = $el.attr('data-node-id') || -1;
            var selectedTree       = $el.attr('data-tree-id') || -1;
            var selectedNodeOrTree = [0, -1].indexOf(selectedNode) !== -1 ? selectedTree : selectedNode;
            var preventFirst       = selectedNode > 0;
            var loadingMask        = new LoadingMask();

            loadingMask.render().$el.appendTo($('#category-tree-container'));

            this.config = {
                core: {
                    animation: 200
                },
                plugins: [
                    'tree_selector',
                    'themes',
                    'json_data',
                    'ui',
                    'crrm',
                    'types'
                ],
                contextmenu: {
                    items: {
                        create: {
                            label: _.__('pim_common.create')
                        },
                        ccp: false,
                        rename: false,
                        remove: false
                    }
                },
                tree_selector: {
                    ajax: {
                        url: Routing.generate(
                            prefixRoute + '_categorytree_listtree',
                            {
                                _format: 'json',
                                select_node_id: selectedNodeOrTree,
                                context: 'manage',
                                with_items_count: 0
                            }
                        )
                    },
                    auto_open_root: true,
                    node_label_field: 'label',
                    no_tree_message: _.__('jstree.no_tree'),
                    preselect_node_id: selectedNode
                },
                themes: {
                    dots: true,
                    icons: true
                },
                json_data: {
                    ajax: {
                        url: Routing.generate(
                            prefixRoute + '_categorytree_children',
                            {
                                _format: 'json',
                                context: 'manage'
                            }
                        ),
                        data: function (node) {
                            // the result is fed to the AJAX request `data` option
                            var id = null;

                            if (node && node !== -1 && node.attr) {
                                id = node.attr('id').replace('node_', '');
                            } else {
                                id = -1;
                            }

                            return {
                                id: id,
                                select_node_id: selectedNode,
                                with_items_count: 0
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
            if ($el.attr('data-editable')) {
                this.config.plugins.push('dnd');
            }
            if ($el.attr('data-creatable')) {
                this.config.plugins.push('contextmenu');
            }
            this.init = function () {
                $el.jstree(this.config).on('move_node.jstree', function (e, data) {
                    var this_jstree = $.jstree._focused();
                    data.rslt.o.each(function (i) {
                        $.ajax({
                            async: false,
                            type: 'POST',
                            url: Routing.generate(prefixRoute + '_categorytree_movenode'),
                            data: {
                                id: $(this).attr('id').replace('node_', ''),
                                parent: data.rslt.cr === -1 ? 1 : data.rslt.np.attr('id').replace('node_', ''),
                                prev_sibling: this_jstree._get_prev(this, true) ?
                                    this_jstree._get_prev(this, true).attr('id').replace('node_', '') : null,
                                position: data.rslt.cp + i,
                                code: data.rslt.name,
                                copy: data.rslt.cy ? 1 : 0
                            },
                            success: function (r) {
                                if (!r.status) {
                                    this_jstree.rollback(data.rlbk);
                                } else {
                                    $(data.rslt.oc).attr('id', r.id);
                                    if (data.rslt.cy && $(data.rslt.oc).children('UL').length) {
                                        data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                                    }
                                }
                            }
                        });
                    });
                }).on('select_node.jstree', function (e, data) {
                    if (!$el.attr('data-editable')) {
                        return;
                    }
                    var id  = data.rslt.obj.attr('id').replace('node_', '');
                    var url = Routing.generate(prefixRoute + '_categorytree_edit', { id: id });
                    if ('#' + url === Backbone.history.location.hash || preventFirst) {
                        preventFirst = false;

                        return;
                    }
                    loadingMask.show();
                    $.ajax({
                        async: true,
                        type: 'GET',
                        url: url + '?content=form',
                        success: function (data) {
                            if (data) {
                                $('#category-form').html(data);
                                Backbone.history.navigate('#' + url, {trigger: false});
                                UI($('#category-form'));
                                loadingMask.hide();
                            }
                        },
                        error: function (jqXHR) {
                            OroError.dispatch(null, jqXHR);
                            loadingMask.hide();
                        }
                    });
                }).on('loaded.jstree', function (event, data) {
                    if (event.namespace === 'jstree') {
                        data.inst.get_tree_select().select2({ width: '100%' });
                    }
                }).on('create.jstree', function (e, data) {
                    $.jstree._focused().lock();
                    var id       = data.rslt.parent.attr('id').replace('node_', '');
                    var url      = Routing.generate(prefixRoute + '_categorytree_create', { parent: id });
                    var position = data.rslt.position;
                    var label    = data.rslt.name;

                    url = url + '?label=' + label + '&position=' + position;
                    loadingMask.show();
                    $.ajax({
                        async: true,
                        type: 'GET',
                        url: url + '&content=form',
                        success: function (data) {
                            if (data) {
                                $('#category-form').html(data);
                                Backbone.history.navigate('#' + url, {trigger: false});
                                loadingMask.hide();
                            }
                        },
                        error: function (jqXHR) {
                            OroError.dispatch(null, jqXHR);
                            loadingMask.hide();
                        }
                    });
                });
            };

            this.init();
        };
    }
);
