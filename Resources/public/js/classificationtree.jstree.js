var jstree = $('#tree').jstree({
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
            PimAjax.ajaxDelete(id+"/remove.json", '');
            data.inst.refresh();
        });
    })
    .bind('select_node.jstree', function (event, node) {
        $('.node-action').remove();
        var btnAdd = '<button id="segment-create" class="btn btn-mini"><i class="icon-plus"></i></button>';
        var btnRemove = '<button id="segment-remove" class="btn btn-mini"><i class="icon-trash"></i></button>';
        node.rslt.obj.before('<div style="display: inline-block; valign: top;" align="right" class="node-action pull-right">'
                + btnAdd
                + btnRemove
            + '</div>');
        $('#segment-create').on('click', function(event) {
            fctCreate();
        });
        $('#segment-remove').on('click', function(event) {
            fctRemove();
        });
    })
    ;