$(tree_id).jstree({
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
        "auto_open_root" : true,
        "node_label_field" : "title"
    },
    "themes" : {
        "dots" : true,
        "icons" : true,
        "themes" : "bap",
        "url" : assetsPath + "/css/style.css"
    },
    "json_data" : {
        "ajax" : {
            "url" : urlChildren,
            "data" : function (node) {
                // the result is fed to the AJAX request `data` option
                var id = null;

                if (node && node != -1) {
                    id = node.attr("id").replace('node_','');
                } else{
                    id = -1;
                }
                return {
                    "id" : id
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
                "valid_children" : "folder"
            }
        }
    },
    "ui" : {
        "select_limit": 1,
        "select_multiple_modifier" : false
    }
})
    .bind('trees_loaded.jstree', function (e, data) {
        $('#'+tree_select_id).select2();
    })
    .bind('loaded.jstree', function(e, data) {
        $(tree_id).jstree('create', null, "last", { 
            "attr": { "class": "jstree-unclassified" },
            "data" : { "title": unclassifiedNodeTitle }
        }, false, true);
    })
    .bind('select_node.jstree', function (e, data) {
        // TODO : Call list content and backbone filter on datagrid
        console.log('select node');
    })
    ;
