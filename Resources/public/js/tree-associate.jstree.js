var jsTreeConfig = {
    "core" : {
        "animation" : 200,
        "html_titles" : true
    },
    "plugins" : [
         "themes", "json_data", "ui", "types","checkbox"
    ],
    "checkbox" : {
        "two_state" : true,
        "real_checkboxes" : true,
        "override_ui" : true,
        "real_checkboxes_names" : function (n) {
            return ["category_" + n[0].id, 1];
        }
    },
    "themes" : {
        "dots" : true,
        "icons" : true,
        "themes" : "bap",
        "url" : assetsPath + "/css/style.css"
    },
    "json_data" : {
        "ajax" : {
            "url" : function (node) {
                var url = window.urlChildren;
                var tree = window.currentTree;
                var treeHasProduct = $('#tree-link-'+tree).hasClass('tree-has-product');

                if ( (!node || (node == -1)) && treeHasProduct )  {
                    // First load of the tree: get the checked categories
                    url = window.urlCheckedCategories.replace('parent/1', 'parent/' + tree);
                }

                return url;
            },
            "data" : function (node) {
                var data = {};

                var tree = window.currentTree;
                var treeHasProduct = $('#tree-link-'+tree).hasClass('tree-has-product');

                if (node && node != -1) {
                    data.id = node.attr("id").replace('node_','');
                } else {
                    if (!treeHasProduct) {
                        data.id = tree;
                    }
                    data.include_parent = "true";
                }

                return data;
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
};

var preSelectedTree = 1;
var currentTree = -1;

function switchTree(treeId) {
    window.currentTree = treeId;
    var tree = $('#tree-' + treeId);
    var treeLink = $('#tree-link-' + treeId);

    $('#trees').find('div').hide(0);
    $('#trees-list').find('a').removeClass('product-selected-tree');

    treeLink.addClass('product-selected-tree');
    
    tree.show(0);

    // If empty, load the associated jstree
    if (tree.children('ul').size() === 0) {
        initTree(treeId);
    }
}

function initTree(treeId) {
    var tree = $('#tree-' + treeId);
    var applyOnTree = $('#apply-on-tree-' + treeId);
    applyOnTree.val(1);
    tree.jstree(window.jsTreeConfig);
}


$('#trees-list').find('a').bind('click', function(event) {
    switchTree(this.id.replace('tree-link-',''));
});
switchTree(preSelectedTree);

