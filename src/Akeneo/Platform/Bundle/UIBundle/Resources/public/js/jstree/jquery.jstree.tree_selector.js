/* global jQuery */
/* jshint unused:vars */
/**
 * Allow to select on which tree to work and manage creation and deletion of trees
 * File: jstree.tree_selector.js
 *
/* Group: jstree tree_selector plugin */
(function ($) {
    'use strict';

    var tree_select_id = 'tree_select';

    $.jstree.plugin('tree_selector', {
        __init: function () {
            this.get_container()
                // Create the tree toolbar and load trees in tree selector
                .bind('init.jstree', $.proxy(function () {
                    var _this = this;
                    var settings = this._get_settings().tree_selector;
                    this.data.tree_selector.ajax = settings.ajax;
                    this.data.tree_selector.data = settings.data;
                    this.data.tree_selector.auto_open_root = settings.auto_open_root;
                    this.data.tree_selector.node_label_field = settings.node_label_field;
                    this.data.tree_selector.no_tree_message = settings.no_tree_message;
                    this.data.tree_selector.preselect_node_id = settings.preselect_node_id;

                    var tree_toolbar = $('<div>', {
                        id: 'tree_toolbar',
                        'class': 'jstree-tree-toolbar'
                    });

                    var tree_select = $('<select>', {
                        id: tree_select_id,
                        'class': 'input-large'
                    });
                    tree_select.addClass('jstree-tree-select');

                    tree_select.bind('change', function () {
                        _this.switch_tree();
                    });

                    tree_toolbar.html(tree_select);
                    this.get_container_ul().before(tree_toolbar);

                    this.load_trees();

                }, this))
                // Rewrite the root node to link it to the selected tree
                .bind('loaded.jstree', $.proxy(function (event) {
                    if (event.namespace === 'jstree') {
                        this.switch_tree();
                    }
                    // Select the node marked as 'toselect' from the server

                }, this))
                .bind('clean_node.jstree', $.proxy(function (e, data) {
                    // Switch to node clicked when requested by the data for this node
                    var _this = this;
                    if (data.rslt.obj) {
                        $.each(data.rslt.obj, function (index, node) {
                            if ($(node).hasClass('toselect')) {
                                _this.select_node($(node));
                                $(node).removeClass('toselect');
                            }
                        });
                    }
                }, this))
                ;
        },
        defaults: {
            ajax: false,
            data: false,
            tree_selector_buttons: false,
            no_tree_message: false,
            node_label_field: 'code',
            preselect_node_id: false
        },
        _fn: {
            refresh: function () {
                this.refresh_trees();

                return this.__call_old();
            },
            switch_tree: function () {
                // Create new root node, place it into the tree and
                // open it if setup to auto_open_root
                var selected_tree = this.get_tree_select().find(':selected');
                var root_node_id = $(selected_tree).prop('value');
                var root_node_code = $(selected_tree).attr('data-code');

                if (!root_node_id || (root_node_id === -1)) {
                    return null;
                }

                var root_node = this._prepare_node(
                    'node_' + root_node_id,
                    selected_tree.text(),
                    root_node_code
                );

                this.get_container_ul().empty();
                this.get_container_ul().append(root_node);

                this.close_node(root_node);
                this.clean_node();

                if (this.data.tree_selector.auto_open_root) {
                    this.open_node(root_node);
                }

                if (this.data.tree_selector.preselect_node_id === root_node_id) {
                    this.select_node(root_node);
                }

                this.get_container().trigger('after_tree_loaded.jstree', root_node_id);
            },
            get_tree_select: function () {
                return $('#' + tree_select_id);
            },
            load_trees: function () {
                var _this = this;
                var trees;

                if (this.data.tree_selector.data) {
                    trees = this._load_data_trees();
                } else if (this.data.tree_selector.ajax) {
                    trees = this._load_ajax_trees();
                } else {
                    throw 'jquery.jstree.tree_selector : Neither data nor ajax settings supplied for trees.';
                }

                this.get_tree_select().empty();

                // In case of no tree loaded, display the no_tree_message
                // if it has been set up
                if (trees.length === 0 && this.data.tree_selector.no_tree_message) {
                    var no_tree_option = $('<option>', {
                        text: this.data.tree_selector.no_tree_message,
                        value: -1,
                        disabled: true,
                        selected: true
                    });
                    this.get_container_ul().empty();
                    this.get_tree_select().append(no_tree_option);

                }

                $.each(trees, function (index, tree) {
                    var option_text = tree[_this.data.tree_selector.node_label_field];

                    var option = $('<option>', {
                        value: tree.id,
                        text: option_text,
                        'data-code': tree.code
                    });

                    if (tree.selected === 'true') {
                        option.prop('defaultSelected', true);
                        _this._get_settings().json_data.data = [
                            {
                                'data': option_text,
                                'state': 'closed',
                                'attr': { 'id': 'node_' + tree.id}
                            }
                        ];
                    }

                    _this.get_tree_select().append(option);
                });

                this.get_container().trigger('trees_loaded.jstree', tree_select_id);
            },
            _load_data_trees: function () {
                var trees_data = this.data.tree_selector.data;

                return $.parseJSON(trees_data);
            },
            _load_ajax_trees: function () {
                var trees_url = this.data.tree_selector.ajax.url;
                var trees_url_parameters = this.data.tree_selector.ajax.parameters;
                var trees = [];

                $.ajax({
                    url: trees_url,
                    async: false,
                    dataType: 'json',
                    data: trees_url_parameters
                }).done(function (ajax_trees) {
                    trees = ajax_trees;
                });

                return trees;

            },
            _prepare_node: function (id, node_name, node_code) {
                var node = $('<li>', {
                    id: id,
                    rel: 'folder',
                    'data-code': node_code
                });

                // Make the node 'openable' by switching back to initial state
                node.prepend('<ins class="jstree-icon">&#160;</ins>');
                node.removeClass('jstree-leaf');
                node.addClass('jstree-close');
                node.addClass('jstree-closed');

                var node_link = $('<a>', {
                    href: '#',
                    text: node_name
                });

                node_link.prepend('<ins class="jstree-icon">&#160;</ins>');
                node.append(node_link);

                return node;
            },
            refresh_trees: function () {
                this.get_tree_select().empty();
                this.load_trees();
                this.switch_tree();
            },
            get_tree_id: function () {
                var root_node = this.get_container_ul().find('li')[0];

                return $(root_node).attr('id');

            }
        }
    });
    // include the tree_selector plugin by default on available plugins list
    $.jstree.defaults.plugins.push('tree_selector');
})(jQuery);
