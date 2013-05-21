var jstree = $('#tree').jstree({
    "core" : {
        "animation" : 200
    },
    "plugins" : [
         "tree_selector", "themes", "json_data", "ui", "crrm", "types"
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
    },
    "ui" : {
        "select_limit": 1,
        "select_multiple_modifier" : false
    }
})
    .bind('trees_loaded.jstree', function(e, tree_select_id) {
        $('#'+tree_select_id).select2();
    })
    .bind("remove.jstree", function (e, data) {
        data.rslt.obj.each(function () {
            var id = $(this).attr("id").replace('node_', '');
            $.post(id+'/remove');
            data.inst.refresh();
        });
    })
    .bind('select_node.jstree', function (event, node) {
        $('.node-action').remove();
        node.rslt.obj.before('<div style="display: inline-block; valign: top;" align="right" class="node-action pull-right">'
                + btnCreate
                + btnUpdate
                + btnRemove
            + '</div>');
        $('#segment-create').on('click', function(event) { fctCreate(); });
        $('#segment-edit').on('click', function(event) { fctEdit(); });
        $('#segment-remove').on('click', function(event) { fctRemove(); });
    })
    ;
