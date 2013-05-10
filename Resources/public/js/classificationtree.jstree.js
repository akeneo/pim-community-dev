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
            "url" : "children",
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
    .bind("create.jstree", function (e, data) {
        var this_jstree = $.jstree._focused();
        var parentId = null;

        if (data.rslt.parent == -1) {
            parentId = this_jstree.get_tree_id();
        } else {
            parentId = data.rslt.parent.attr("id");
        }

        $.post(
            "create-node",
            {
                "id" : parentId,
                "position" : data.rslt.position,
                "title" : data.rslt.name,
                "type" : data.rslt.obj.attr("rel")
            },
            function (r) {
                if(r.status) {
                    $(data.rslt.obj).attr("id", r.id);
                }
                else {
                    this_jstree.rollback(data.rlbk);
                }
            }
        );
    })
    .bind("remove.jstree", function (e, data) {
        data.rslt.obj.each(function () {
            var id = $(this).attr("data-id");
            $.ajax({
                async : false,
                type: 'GET',
                url: "remove-node/"+id,
                success : function (r) {
                    console.log(r);
                    if(!r.status) {
                        data.inst.refresh();
                    }
                }
            });
        });
    })
    .bind("rename.jstree", function (e, data) {
        $.post(
            "rename-node",
            {
                "id" : data.rslt.obj.attr("id").replace('node_',''),
                "title" : data.rslt.new_name
            },
            function (r) {
                if(!r.status) {
                    this_jstree.rollback(data.rlbk);
                }
            }
        );
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
        $.fn.renderItemList(nodeId);
    });

$.fn.removeTree = function(tree_id) {
    var this_jstree = $.jstree._focused();

    $.ajax({
        async : false,
        type: 'POST',
        url: "remove-tree",
        data : {
            "id" : tree_id
        },
        success: function(data) {
            this_jstree.refresh_trees();
        }
    });
};

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
        url: "list-items",
        data : {
            "segment_id" : segmentId
        },
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

$.fn.removeItem = function(segmentId, itemId) {
    $.ajax({
        async : false,
        type: 'POST',
        url: "remove-item",
        data : {
            "segment_id" : segmentId,
            "item_id" : itemId
        },
        success : function (r) {
            renderItemList(segmentId);
        }
    });
};

$(function () {
    $("#tree_menu button").click(function () {
        var tree_id = "#tree";
        switch(this.id) {
            case "refresh":
                $(tree_id).jstree('refresh',-1);
                break;
            case "add":
                $(tree_id).jstree("create", null, "last", { "attr" : { "rel" : this.id.toString().replace("add_", "") } });
                break;
            case "search":
                $(tree_id).jstree("search", $("#search_text").val());
                break;
            case "clear_search":
                $(tree_id).jstree("clear_search");
                break;
            case "rename":
                $(tree_id).jstree("rename");
                break;
            case "remove":
                $(tree_id).jstree("remove");
                break;
            case "add_segment":
                $(tree_id).jstree("create");
                break;
            case "create_tree":
                $.fn.createTree($("#create_tree_title").val(), '#trees','#tree','#selectedTreeId');
                break;
            case "add_item":
                node = $.jstree._focused().get_selected();
                nodeId = node.attr('id');
                segmentId = nodeId.replace('node_','');
                itemId = $("#add_item_id").val();

                $.fn.addItem(segmentId, itemId);
                break;
            case "remove_item":
                node = $.jstree._focused().get_selected();
                nodeId = node.attr('id');
                segmentId = nodeId.replace('node_','');
                itemId = $("#remove_item_id").val();

                $.fn.removeItem(segmentId, itemId);
                break;
        }
   });
});
