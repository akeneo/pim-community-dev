$('#tree').jstree({
    "core" : {
        "animation" : 200
    },
    "plugins" : [
        "tree_selector", "themes", "json_data", "ui", "crrm", "cookies", "dnd", "search", "types", "hotkeys"
    ],
    "tree_selector" : {
        "ajax" : {
            "url" : "list-tree.json"
        },
        "auto_open_root" : true
    },
    "themes" : {
        "dots" : true,
        "icons" : true,
        "themes" : "bap",
        "url" : assetsPath + "/css/style.css"
    },
    "json_data" : {
        "data" : [
            {
                "data": "Loading root...",
                "state": "closed",
                "attr" : { "id" : "node_1"}
            }
        ],
        "ajax" : {
            "url" : "children.json",
            "data" : function (node) {
                // the result is fed to the AJAX request `data` option
                return {
                    "id" : node.attr("id").replace('node_','')
                };
            }
        }
    },
    "search" : {
        "ajax" : {
            "url" : "search",
            "data" : function (str) {
                return {
                    "tree_root_id": $.jstree._focused().get_tree_id(),
                    "search_str" : str
                };
            }
        }
    },
    "types" : {
        "max_depth" : -2,
        "max_children" : -2,
        "valid_children" : [ "folder" ],
        "types" : {
            "default" : {
                "valid_children" : "folder",
                "icon" : {
                    "image" : assetsPath + "images/folder.png"
                }
            },
            "folder" : {
                "icon" : {
                    "image" : assetsPath + "images/folder.png"
                }
            }
        }
    }
})
    .bind('trees_loaded.jstree', function(e, tree_select_id) {
        $('#'+tree_select_id).uniform();
    })
    .bind("remove.jstree", function (e, data) {
        data.rslt.obj.each(function () {
            var id = $(this).attr("id").replace('node_', '');
            $.ajax({
                async : true,
                type: 'GET',
                url: "remove/"+id,
                success : function (r) {
                    if(r.status) {
                        data.inst.refresh();
                    } else {
                        var alert = new Backbone.BootstrapModal({
                            allowCancel: false,
                            title: "{{ 'Alert message' | trans }}",
                            content: "{{ 'Impossible to delete root node' | trans }}"
                        });
                        alert.open();
                        $.jstree.rollback(data.inst.rlbk);
                    }
                },
                error: function (r) {
                    // TODO : Show popup to know problem !
                    $.jstree.rollback(data.inst.rlbk);
                }
            });
        });
    })
    .bind("move_node.jstree", function (e, data) {
        var this_jstree = $.jstree._focused();
        data.rslt.o.each(function (i) {

            $.ajax({
                async : false,
                type: 'POST',
                url: "move-node",
                data : {
                    "id" : $(this).attr("id").replace('node_',''),
                    "parent" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace('node_',''),
                    "prev_sibling" : this_jstree._get_prev(this, true) ? this_jstree._get_prev(this, true).attr('id').replace('node_','') : null,
                    "position" : data.rslt.cp + i,
                    "title" : data.rslt.name,
                    "copy" : data.rslt.cy ? 1 : 0
                },
                success : function (r) {
                    if(!r.status) {
                        this_jstree.rollback(data.rlbk);
                    }
                    else {
                        $(data.rslt.oc).attr("id", r.id);
                        if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
                            data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                        }
                    }
                }
            });
        });
    })
    .bind("select_node.jstree", function (e, data) {
        var this_jstree = $.jstree._focused();

        var a = this_jstree.get_selected();
        var nodeId = a.attr('id').replace('node_','');
        console.log('select node jstree');
        $.fn.renderItemList(nodeId);
    });

//$.fn.removeTree = function(tree_id) {
//    var this_jstree = $.jstree._focused();
//
//    $.ajax({
//        async : false,
//        type: 'POST',
//        url: "remove/"+tree_id,
//        success: function(data) {
//            this_jstree.refresh_trees();
//        }
//    });
//};

$.fn.createTree = function (title) {
    var this_jstree = $.jstree._focused();

    $.ajax({
        async : false,
        type: 'POST',
        url: "create-tree",
        data : {
            "title" : title
        },
        success: function(data) {
            this_jstree.refresh_trees();
        }
    });
};

$.fn.renderItemList = function renderItemList(segmentId) {
    $.ajax({
        async : false,
        type: "GET",
        url: "list-items.json/"+segmentId,
        success: function(data) {
            var table = $('#product_grid');
            table.empty();

            if (data.length > 0) {
                var headers_line = $('<tr>');

                for (var attribute in data[0]) {
                    var header = $('<th>', {
                        text : attribute
                    });
                    headers_line.append(header);
                }

                table.append(headers_line);

                $.each(data, function(i,item) {
                    var data_line = $('<tr>');

                    for (var attribute in item) {
                        var field = $('<td>', {
                            text : item[attribute]
                        });
                        data_line.append(field);
                    }
                    table.append(data_line);
                });
            }
        }
    });
};

$.fn.addItem = function(segmentId, itemId) {
    $.ajax({
        async : false,
        type: 'POST',
        url: "add-item",
        data : {
            "segment_id" : segmentId,
            "item_id" : itemId
        },
        success : function (r) {
            renderItemList(segmentId);
        }
    });
};

//$.fn.removeItem = function(segmentId, itemId) {
//    $.ajax({
//        async : false,
//        type: 'POST',
//        url: "remove-item",
//        data : {
//            "segment_id" : segmentId,
//            "item_id" : itemId
//        },
//        success : function (r) {
//            renderItemList(segmentId);
//        }
//    });
//};
