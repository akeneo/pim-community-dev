/**
 * Allow to select on which tree to work and manage creation and deletion of trees
 * File: jstree.tree_selector.js
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
/* Group: jstree tree_selector plugin */
(function ($) {
    var tree_select_id = "tree_select";

    $.jstree.plugin("tree_selector", {
        __init : function () {
            this.get_container()
                // Create the tree toolbar and load trees in tree selector
                .bind("init.jstree", $.proxy(function () {
                    var settings = this._get_settings().tree_selector;
                    this.data.tree_selector.ajax = settings.ajax;
                    this.data.tree_selector.data = settings.data;
                    this.data.tree_selector.auto_open_root = settings.auto_open_root;
                    this.data.tree_selector.no_tree_message = settings.no_tree_message;
                    this.data.tree_selector.node_label_field = settings.node_label_field;

                    var tree_toolbar = $('<div>', {
                        id: 'tree_toolbar'
                    });
                    // The class option can be used above as class is a reserved word
                    tree_toolbar.addClass('jstree-tree-toolbar');

                    var tree_select = $('<select>', {
                        id: tree_select_id,
                        style: 'width:90%' 
                    });
                    tree_select.addClass('jstree-tree-select');

                    var this_jstree = this;
                    tree_select.bind('change', function() {
                        this_jstree.switch_tree();
                    });

                    tree_toolbar.html(tree_select);
                    this.get_container_ul().before(tree_toolbar);

                    this.load_trees();

                }, this))
                // Rewrite the root node to link it to the selected tree
                .bind("loaded.jstree", $.proxy(function () {
                    this.switch_tree();

                }, this))
                /*
                .bind("load_node.jstree", $.proxy(function (e,data) {
                    alert("load node event");
                }, this))*/
                ;
        },
        defaults : {
            ajax : false,
            data : false,
            tree_selector_buttons : false,
            no_tree_message : false,
            node_label_field : 'code'
        },
        _fn : {
            refresh : function (obj) {
                this.refresh_trees();

                return this.__call_old();
            },
            switch_tree : function () {
                // Create new root node, place it into the tree and
                // open it if setup to auto_open_root
                var selected_tree = this.get_tree_select().find(':selected');
                var root_node_id = $(selected_tree).attr('value');

                if (!root_node_id) {
                    return null;
                }

                root_node = this._prepare_node(
                    root_node_id,
                    selected_tree.text()
                );

                this.get_container_ul().empty();
                this.get_container_ul().append(root_node);

                this.close_node(root_node);
                this.clean_node();

                if (this.data.tree_selector.auto_open_root) {
                    this.open_node(root_node);
                }
            },
            get_tree_select : function () {
                return $("#" + tree_select_id);
            },
            load_trees: function () {
                var trees;

                if (this.data.tree_selector.data) {
                    trees = this._load_data_trees();
                } else if (this.data.tree_selector.ajax) {
                    trees = this._load_ajax_trees();
                } else {
                    throw "jquery.jstree.tree_selector : Neither data nor ajax settings supplied for trees.";
                }

                this.get_tree_select().empty();

                // In case of no tree loaded, display the no_tree_message
                // if set up
                if (trees.length === 0 && this.data.tree_selector.no_tree_message) {
                    var no_tree_option = $('<option>', {
                        text: this.data.tree_selector.no_tree_message,
                        value: -1,
                        disabled: true,
                        selected: true
                    });
                    this.get_tree_select().append(no_tree_option);
                }

                var this_jstree = this;
                $.each(trees, function (index, tree) {
                    var option_text = tree[this_jstree.data.tree_selector.node_label_field];

                    var option = $('<option>', {
                        value: tree.id,
                        text: option_text
                    });

                    if (index === 0) {
                        option.prop('defaultSelected', true);
                        this_jstree._get_settings().json_data.data = [
                            {
                                "data": option_text,
                                "state": "closed",
                                "attr" : { "id" : tree.id}
                            }
                        ];
                    }
                    this_jstree.get_tree_select().append(option);
                });

                this.get_container().trigger('trees_loaded.jstree', tree_select_id);
            },
            _load_data_trees: function () {
                var trees_data = this.data.tree_selector.data;

                return $.parseJSON(trees_data);
            },
            _load_ajax_trees: function () {
                var trees_url = this.data.tree_selector.ajax.url;
                var trees = [];

                $.ajax({
                    url: trees_url,
                    async: false,
                    dataType: 'json'
                }).done( function(ajax_trees) {
                    trees = ajax_trees;
                });

                return trees;

            },
            _prepare_node: function (id, node_name) {
                var node = $('<li>', {
                    id: id,
                    rel: 'folder'
                });

                // Make the node "openable" by switching back to initial state
                node.prepend("<ins class='jstree-icon'>&#160;</ins>");
                node.removeClass('jstree-leaf');
                node.addClass('jstree-close');
                node.addClass('jstree-closed');

                var node_link = $('<a>', {
                    href: "#",
                    text: node_name
                });
        
                node_link.prepend("<ins class='jstree-icon'>&#160;</ins>");
                node.append(node_link);

                return node;
            },
            refresh_trees: function() {
                this.get_tree_select().empty();
                this.load_trees();
                this.switch_tree();
            },
            get_tree_id: function() {
                root_node = this.get_container_ul().find('li')[0];

                return $(root_node).attr('id');

            }
        }
    });
    // include the tree_selector plugin by default on available plugins list
    $.jstree.defaults.plugins.push("tree_selector");
})(jQuery);
