
var preselect_node_id = null;

// Get the current node id from URL when in edit form
var edit_url_pattern = /edit\/[0-9]+/;

if (edit_url_pattern.test(window.location.pathname)) {
    var url_parts = window.location.pathname.split('/');
    preselect_node_id = url_parts[url_parts.length-1];
}

// Get the current node id from URL when in create form
var create_url_pattern = /create\/[0-9]+/;

if (create_url_pattern.test(window.location.pathname)) {
    var url_parts = window.location.pathname.split('/');
    preselect_node_id = url_parts[url_parts.length-1];
}

// Case of return from save: the node id will be positionned on the node
// request parameter
var node_param_pattern = /node=[0-9]+/;
if (node_param_pattern.test(window.location.search)) {
    var search_parts = window.location.search.replace('?','').split('&');

    var node_param_pattern_strict = /^node=[0-9]+$/;
    var i = 0;
    var found_node = false;

    while ( (i < search_parts.length) && !found_node) {
        if (node_param_pattern_strict.test(search_parts[i])) {
            var param_node_parts = search_parts[i].split('=');
            preselect_node_id = param_node_parts[param_node_parts.length - 1];
            found_node = true;
        }
    }
}

$(tree_id).jstree({
    "core" : {
        "animation" : 200
    },
    "plugins" : [
         "tree_selector", "themes", "json_data", "ui", "crrm", "types"
    ],
    "tree_selector" : {
        "ajax" : {
            "url" : urlListTree,
            "parameters" : {"select_node_id" : window.preselect_node_id}
        },
        "auto_open_root" : true,
        "node_label_field" : "title",
        "no_tree_message" : window.no_tree_message,
        "preselect_node_id" : window.preselect_node_id
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
                    "id" : id,
                    "select_node_id" : window.preselect_node_id
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
    .bind('loaded.jstree', function(event, data) {
        if (event.namespace == 'jstree') {
            data.inst.get_tree_select().select2();
        }
    })
    .bind("remove.jstree", function (event, data) {
        data.rslt.obj.each(function () {
            var id = $(this).attr("id").replace('node_', '');
            var url = urlRemove.replace("#ID#", id);
            PimAjax.ajaxPost(url, '');
            data.inst.refresh();
        });
    })
    ;
