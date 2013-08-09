var Pim = Pim || {};
Pim.tree = Pim.tree || {};

Pim.tree.associate = function(elementId) {
    var $el = $('#'+elementId);
    if (!$el || !$el.length || !_.isObject($el)) {
        throw new Error('Unable to instantiate tree on this element');
    }
    var currentTree = -1,
    assetsPath = $el.attr('data-assets-path'),
    checkedCategoriesUrl = $el.attr('data-checked-categories-url'),
    childrenUrl = $el.attr('data-children-url'),
    selectedTree = $el.attr('data-selected-tree'),
    self = this;

    this.config = {
        "core" : {
            "animation" : 200,
            "html_titles" : true
        },
        "plugins" : [
             "themes",
             "json_data",
             "ui",
             "types",
             "checkbox"
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
            "url" : assetsPath + "css/style.css"
        },
        "json_data" : {
            "ajax" : {
                "url" : function (node) {
                    var treeHasProduct = $('#tree-link-'+currentTree).hasClass('tree-has-product');

                    if ( (!node || (node == -1)) && treeHasProduct )  {
                        // First load of the tree: get the checked categories
                        return checkedCategoriesUrl.replace('parent/1', 'parent/' + currentTree);
                    }

                    return childrenUrl;
                },
                "data" : function (node) {
                    var data = {};

                    var treeHasProduct = $('#tree-link-'+currentTree).hasClass('tree-has-product');

                    if (node && node != -1) {
                        data.id = node.attr("id").replace('node_','');
                    } else {
                        if (!treeHasProduct) {
                            data.id = currentTree;
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

    this.switchTree = function(treeId) {
        currentTree = treeId;
        var $tree = $('#tree-' + treeId);

        $('#trees').find('div').hide();
        $('#trees-list').find('li').removeClass('active');
        $('#tree-link-' + treeId).parent().addClass('active');

        $(".tree[data-tree-id="+treeId+"]").show();
        $tree.show();

        // If empty, load the associated jstree
        if ($tree.children('ul').size() === 0) {
            self.initTree(treeId);
        }
    };

    this.initTree = function(treeId) {
        var $tree = $('#tree-' + treeId);
        var applyOnTree = $('#apply-on-tree-' + treeId);
        applyOnTree.val(1);
        $tree.jstree(self.config);
    };

    this.bindEvents = function() {
        $('#trees-list a').on('click', function() {
            self.switchTree(this.id.replace('tree-link-',''));
        });
    };

    this.init = function() {
        self.switchTree(selectedTree);
        self.bindEvents();
    };

    this.init();
};
