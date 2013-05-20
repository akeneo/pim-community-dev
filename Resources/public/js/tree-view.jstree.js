var jstree = $('#tree').jstree({
    "core" : {
        "animation" : 200
    },
    "plugins" : [
         "tree_selector", "themes", "json_data", "ui", "crrm", "types"
    ],
    "tree_selector" : {
        "ajax" : {
            "url" : urlListTree
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
            "url" : urlChildren,
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
        $('#'+tree_select_id).uniform();
    })
    .bind('select_node.jstree', function (event, node) {
        // TODO : Call list content
    })
    ;