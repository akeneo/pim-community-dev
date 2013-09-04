var Pim = Pim || {};
Pim.tree = Pim.tree || {};

Pim.tree.manage = function(elementId) {
    'use strict';
    var $el = $('#'+elementId);
    if (!$el || !$el.length || !_.isObject($el)) {
        throw new Error('Unable to instantiate tree on this element');
    }
    var selectedNode = $el.attr('data-node-id') || -1,
    noTreeMessage    = $el.attr('data-no-tree-message'),
    assetsPath       = $el.attr('data-assets-path'),
    listtreeUrl      = $el.attr('data-listtree-url'),
    childrenUrl      = $el.attr('data-children-url'),
    createUrl        = $el.attr('data-create-url'),
    editUrl          = $el.attr('data-edit-url'),
    moveUrl          = $el.attr('data-move-url'),
    editLabel        = $el.attr('data-edit-label'),
    loadingMask      = new Oro.LoadingMask();

    loadingMask.render().$el.appendTo($('#container'));

    this.config = {
        'core': {
            'animation': 200
        },
        'plugins': [
             'tree_selector',
             'themes',
             'json_data',
             'ui',
             'crrm',
             'types',
             'dnd',
             'contextmenu'
        ],
        contextmenu: {
            items: {
                'edit': {
                    'label': editLabel,
                    'action': function (obj) {
                        var id = obj.attr('id').replace('node_', '');
                        var url = editUrl.replace('0', id);
                        Pim.navigate(url);
                    }
                },
                'ccp': false,
                'rename': false,
                'remove': false
            }
        },
        'tree_selector': {
            'ajax': {
                'url': listtreeUrl,
                'parameters': {'select_node_id': selectedNode}
            },
            'auto_open_root': true,
            'node_label_field': 'title',
            'no_tree_message': noTreeMessage,
            'preselect_node_id': selectedNode
        },
        'themes': {
            'dots': true,
            'icons': true,
            'themes': 'bap',
            'url': assetsPath + '/css/style.css'
        },
        'json_data': {
            'ajax': {
                'url': childrenUrl,
                'data': function (node) {
                    // the result is fed to the AJAX request `data` option
                    var id = null;

                    if (node && node != -1) {
                        id = node.attr('id').replace('node_','');
                    } else{
                        id = -1;
                    }
                    return {
                        'id': id,
                        'select_node_id': selectedNode,
                        'with_products_count': 'true'
                    };
                }
            }
        },
        'types': {
            'max_depth': -2,
            'max_children': -2,
            'valid_children': [ 'folder' ],
            'types': {
                'default': {
                    'valid_children': 'folder'
                }
            }
        },
        'ui': {
            'select_limit': 1,
            'select_multiple_modifier': false
        }
    };

    this.init = function() {
        $el.jstree(this.config)
        .bind('move_node.jstree', function (e, data) {
            var this_jstree = $.jstree._focused();
            data.rslt.o.each(function (i) {
                $.ajax({
                    async: false,
                    type: 'POST',
                    url: moveUrl,
                    data: {
                        'id': $(this).attr('id').replace('node_',''),
                        'parent': data.rslt.cr === -1 ? 1 : data.rslt.np.attr('id').replace('node_',''),
                        'prev_sibling': this_jstree._get_prev(this, true) ? this_jstree._get_prev(this, true).attr('id').replace('node_','') : null,
                        'position': data.rslt.cp + i,
                        'code': data.rslt.name,
                        'copy': data.rslt.cy ? 1 : 0
                    },
                    success: function (r) {
                        if(!r.status) {
                            this_jstree.rollback(data.rlbk);
                        }
                        else {
                            $(data.rslt.oc).attr('id', r.id);
                            if(data.rslt.cy && $(data.rslt.oc).children('UL').length) {
                                data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                            }
                        }
                    }
                });
            });
        })
        .bind('select_node.jstree', function (e, data) {
            var id = data.rslt.obj.attr('id').replace('node_',''),
            url = Routing.generate('pim_catalog_categorytree_edit', { id: id });
            if ('#url=' + url === Backbone.history.location.hash) {
                return;
            }
            loadingMask.show();
            $.ajax({
                async: true,
                type: 'GET',
                url: url,
                success: function (data) {
                    if (data) {
                        $('#category-form').html(data);
                        Backbone.history.navigate('url=' + url, {trigger: false});
                        loadingMask.hide();
                    }
                },
                error: function(jqXHR) {
                    Oro.BackboneError.Dispatch(null, jqXHR);
                    loadingMask.hide();
                }
            });
        })
        .bind('loaded.jstree', function(event, data) {
            if (event.namespace == 'jstree') {
                data.inst.get_tree_select().select2({ width: '100%' });
            }
        })
        .bind('create.jstree', function (e, data) {
            var id = data.rslt.parent.attr('id').replace('node_', ''),
            url = id ? createUrl + '/' + id : createUrl,
            position = data.rslt.position,
            title = data.rslt.name;

            url = url + '?' + 'title=' + title + '&position=' + position;
            Pim.navigate(url);
        });
    };

    this.init();
};
