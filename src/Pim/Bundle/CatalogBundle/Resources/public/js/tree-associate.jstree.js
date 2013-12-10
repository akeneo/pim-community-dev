define(
    ['jquery', 'underscore', 'routing', 'oro/mediator', 'jquery.jstree'],
    function ($, _, Routing, mediator) {
        'use strict';

        return function (elementId, hiddenCategoryId) {
            var $el = $(elementId);
            if (!$el || !$el.length || !_.isObject($el)) {
                throw new Error('Unable to instantiate tree on this element');
            }
            var self         = this,
                currentTree  = -1,
                id           = $el.attr('data-id'),
                selectedTree = $el.attr('data-selected-tree'),
                dataLocale   = $el.attr('data-datalocale'),
                locked       = false;

            this.config = {
                core: {
                    animation: 200,
                    html_titles: true
                },
                plugins: [
                    'themes',
                    'json_data',
                    'ui',
                    'types',
                    'checkbox'
                ],
                checkbox: {
                    two_state: true,
                    real_checkboxes: true,
                    override_ui: true,
                    real_checkboxes_names: function (n) {
                        return ['category_' + n[0].id, 1];
                    }
                },
                themes: {
                    dots: true,
                    icons: true
                },
                json_data: {
                    ajax: {
                        url: function (node) {
                            var treeHasProduct = $('#tree-link-' + currentTree).hasClass('tree-has-product');

                            if ((!node || (node === -1)) && treeHasProduct) {
                                // First load of the tree: get the checked categories
                                return Routing.generate('pim_catalog_product_listcategories', { id: id, category_id: currentTree, _format: 'json', dataLocale: dataLocale });
                            }

                            return Routing.generate('pim_catalog_categorytree_children', { _format: 'json', dataLocale: dataLocale });
                        },
                        data: function (node) {
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
                            // Lock the loaded tree if the state is locked
                            if (locked) {
                                this.lock();
                            }
                        }
                    }
                },
                types: {
                    max_depth: -2,
                    max_children: -2,
                    valid_children: [ 'folder' ],
                    types: {
                        'default': {
                            valid_children: 'folder'
                        }
                    }
                },
                ui: {
                    select_limit: 1,
                    select_multiple_modifier: false
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

                $tree.bind('check_node.jstree', function (e, d) {
                    if (d.inst.get_checked() && $(d.rslt.obj[0]).hasClass('jstree-root') === false) {
                        var selected = $(hiddenCategoryId).val();
                        selected = selected.length > 0 ? selected.split(',') : [];
                        var id = d.rslt.obj[0].id.replace('node_', '');
                        if ($.inArray(id, selected) < 0) {
                            selected.push(id);
                            selected = $.unique(selected);
                            selected = selected.join(',');
                            $(hiddenCategoryId).val(selected).trigger('change');
                            var treeId = e.target.id;
                            var treeLinkId = treeId.replace('-', '-link-');
                            $('#'+treeLinkId+' i').removeClass('gray');
                            $('#'+treeLinkId+' i').addClass('green');
                        }
                    }
                });

                $tree.bind('uncheck_node.jstree', function (e, d) {
                    if (d.inst.get_checked()) {
                        var selected = $(hiddenCategoryId).val();
                        selected = selected.split(',');
                        var id = d.rslt.obj[0].id.replace('node_', '');
                        selected.splice($.inArray(id, selected),1);
                        selected = selected.join(',');
                        $(hiddenCategoryId).val(selected).trigger('change');
                        var treeId = e.target.id;
                        if ($('#'+treeId).jstree('get_checked').length === 0) {
                            var treeLinkId = treeId.replace('-', '-link-');
                            $('#'+treeLinkId+' i').removeClass('green');
                            $('#'+treeLinkId+' i').addClass('gray');
                        }
                    }
                });
            };

            var setLocked = function () {
                $('#trees-list').find('a').each(function () {
                    var ref = $.jstree._reference(this.id.replace('tree-link-', '#tree-'));
                    if (ref) {
                        if (locked) {
                            ref.lock();
                        } else {
                            ref.unlock();
                        }
                    }
                });
            };

            this.lock = function() {
                locked = true;
                setLocked();
            };

            this.unlock = function() {
                locked = false;
                setLocked();
            };

            this.bindEvents = function () {
                $('#trees-list').on('click', 'a', function () {
                    self.switchTree(this.id.replace('tree-link-', ''));
                });
                mediator.on('jstree:lock', this.lock);
                mediator.on('jstree:unlock', this.unlock);
            };

            this.init = function () {
                self.switchTree(selectedTree);
                self.bindEvents();
            };

            this.init();
        };
    }
);
