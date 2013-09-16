define(
    ['jquery', 'underscore', 'routing', 'jquery.jstree'],
    function ($, _, Routing) {
        'use strict';

        return function (elementId) {
            var $el = $(elementId);
            if (!$el || !$el.length || !_.isObject($el)) {
                throw new Error('Unable to instantiate tree on this element');
            }
            var self         = this,
                currentTree  = -1,
                id           = $el.attr('data-id'),
                selectedTree = $el.attr('data-selected-tree'),
                dataLocale   = $el.attr('data-datalocale');

            this.config = {
                'core': {
                    'animation': 200,
                    'html_titles': true
                },
                'plugins': [
                    'themes',
                    'json_data',
                    'ui',
                    'types',
                    'checkbox'
                ],
                'checkbox': {
                    'two_state': true,
                    'real_checkboxes': true,
                    'override_ui': true,
                    'real_checkboxes_names': function (n) {
                        return ['category_' + n[0].id, 1];
                    }
                },
                'themes': {
                    'dots': true,
                    'icons': true
                },
                'json_data': {
                    'ajax': {
                        'url': function (node) {
                            var treeHasProduct = $('#tree-link-' + currentTree).hasClass('tree-has-product');

                            if ((!node || (node === -1)) && treeHasProduct) {
                                // First load of the tree: get the checked categories
                                return Routing.generate('pim_catalog_product_listcategories', { 'id': id, 'category_id': currentTree, '_format': 'json', 'dataLocale': dataLocale });
                            }

                            return Routing.generate('pim_catalog_categorytree_children', { '_format': 'json', 'dataLocale': dataLocale });
                        },
                        'data': function (node) {
                            var data           = {},
                                treeHasProduct = $('#tree-link-' + currentTree).hasClass('tree-has-product');

                            if (node && node !== -1) {
                                data.id = node.attr('id').replace('node_', '');
                            } else {
                                if (!treeHasProduct) {
                                    data.id = currentTree;
                                }
                                data.include_parent = 'true';
                            }

                            return data;
                        },
                        complete: function () {
                            // Disable the root checkbox
                            $('.jstree-root>input.jstree-real-checkbox').attr('disabled', 'disabled');
                        }
                    }
                },
                'types': {
                    'max_depth': -2,
                    'max_children': -2,
                    'valid_children': [ 'folder' ],
                    'types': {
                        'default': {
                            'valid_children': 'folder'
                        }
                    }
                },
                'ui': {
                    'select_limit': 1,
                    'select_multiple_modifier': false
                }
            };

            this.switchTree = function (treeId) {
                currentTree = treeId;
                var $tree = $('#tree-' + treeId);

                $('#trees').find('div').hide();
                $('#trees-list').find('li').removeClass('active');
                $('#tree-link-' + treeId).parent().addClass('active');

                $('.tree[data-tree-id=' + treeId + ']').show();
                $tree.show();

                // If empty, load the associated jstree
                if ($tree.children('ul').size() === 0) {
                    self.initTree(treeId);
                }
            };

            this.initTree = function (treeId) {
                var $tree = $('#tree-' + treeId);
                $('#apply-on-tree-' + treeId).val(1);
                $tree.jstree(self.config);
            };

            this.bindEvents = function () {
                $('#trees-list a').on('click', function () {
                    self.switchTree(this.id.replace('tree-link-', ''));
                });
            };

            this.init = function () {
                self.switchTree(selectedTree);
                self.bindEvents();
            };

            this.init();
        };
    }
);
