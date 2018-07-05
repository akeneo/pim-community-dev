'use strict';
/**
 * Categories selector tree
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'oro/translator',
        'oro/loading-mask',
        'pim/i18n',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/template/filter/product/category/selector',
        'jquery.jstree'
    ],
    function ($, _, Backbone, Routing, __, LoadingMask, i18n, FetcherRegistry, UserContext, template) {
        return Backbone.View.extend({
            template: _.template(template),

            config: {
                core: {
                    animation: 200,
                    html_titles: true,
                    strings: { loading:  __('pim_common.loading') }
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
                    real_checkboxes: true
                },
                themes: {
                    dots: true,
                    icons: true
                },
                types: {
                    max_depth: -2,
                    max_children: -2,
                    valid_children: ['folder'],
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
            },

            currentTree: null,

            attributes: {
                categories: []
            },

            /**
             * Callback called when a node is checked in jstree
             *
             * @param {Object} data
             */
            checkNode: function (data) {
                var code = String(data.rslt.obj.data('code'));
                // All products case
                if ('' === code) {
                    // Uncheck other nodes
                    data.inst.get_container_ul().find('li.jstree-checked:not(.jstree-all)').each(function () {
                        data.inst.uncheck_node(this);
                    });

                    this.attributes.categories = [];
                } else {
                    if (!_.contains(this.attributes.categories, code)) {
                        this.attributes.categories.push(code);
                    }

                    // Uncheck "All products" if checked
                    data.inst.uncheck_node(data.inst.get_container_ul().find('li.jstree-all'));
                }
            },

            /**
             * Callback called when a node is unchecked in jstree
             *
             * @param {Object} data
             */
            uncheckNode: function (data) {
                var code = data.rslt.obj.data('code').toString();

                if ('' !== code) {
                    this.attributes.categories = _.without(this.attributes.categories, code);
                }
            },

            /**
             * Callback called when a node is loaded in jstree
             *
             * @param {Object} data
             */
            loadNode: function (data) {
                var node = data.rslt.obj;

                if (-1 === node) {
                    // Add the All products checkbox
                    data.inst.create_node(data.inst.get_container(), 'last', {
                        attr: {
                            'id': 'node_',
                            'class': 'jstree-unclassified jstree-all separated',
                            'data-code': ''
                        },
                        data: { title: __('jstree.all') }
                    }, function ($node) {
                        if (0 === this.attributes.categories.length) {
                            data.inst.check_node($node);
                        }
                    }.bind(this), true);
                } else if (_.contains(this.attributes.categories, node.data('code'))) {
                    data.inst.check_node(node);
                }
            },

            /**
             * Render the tree in the element's HTML when the channel category is fetched and bind events from jstree
             */
            render: function () {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.$el.parent());
                loadingMask.show();

                FetcherRegistry.initialize().then(function () {
                    FetcherRegistry.getFetcher('channel')
                        .fetch(this.attributes.channel)
                        .then(function (channel) {
                            return $.when(
                                FetcherRegistry.getFetcher('category').fetch(channel.category_tree)
                            ).then(function (category) {
                                this.$el.html(this.template({
                                    tree: category,
                                    label: i18n.getLabel(
                                        category.labels,
                                        UserContext.get('uiLocale'),
                                        category.code
                                    )
                                }));

                                var selectedCategories = this.attributes.categories;

                                this.$('.root').jstree(_.extend(this.config, {
                                    json_data: {
                                        ajax: {
                                            dataType: 'json',
                                            method: 'POST',
                                            url: function (node) {
                                                if (-1 === node && 0 < selectedCategories.length) {
                                                    // First load of the tree: get the checked categories
                                                    return Routing.generate(
                                                        'pim_enrich_category_rest_list_selected_children',
                                                        {
                                                            identifier: category.code
                                                        }
                                                    );
                                                }

                                                return Routing.generate('pim_enrich_categorytree_children', {
                                                    _format: 'json'
                                                });
                                            }.bind(this),
                                            data: function (node) {
                                                if (-1 === node) {
                                                    return {
                                                        id: this.get_container().data('tree-id'),
                                                        selected: selectedCategories
                                                    };
                                                }

                                                return {id: node.attr('id').replace('node_', '')};
                                            }
                                        }
                                    }
                                }))
                                .on('check_node.jstree', function (event, data) {
                                    this.checkNode(data);
                                }.bind(this))
                                .on('uncheck_node.jstree', function (event, data) {
                                    this.uncheckNode(data);
                                }.bind(this))
                                .on('load_node.jstree', function (event, data) {
                                    this.loadNode(data);
                                }.bind(this));
                            }.bind(this));
                        }.bind(this))
                        .done(function () {
                            this.$el.parent().find('.loading-mask').remove();
                        }.bind(this));
                }.bind(this));
            }
        });
    }
);
