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
        'pim/fetcher-registry',
        'text!pim/template/product-export/categories-selector-tree',
        'jquery.jstree'
    ],
    function ($, _, Backbone, Routing, __, LoadingMask, FetcherRegistry, template) {

        return Backbone.View.extend({

            template: _.template(template),

            config: {
                core: {
                    animation: 200,
                    html_titles: true,
                    strings: { loading:  __('jstree.loading') }
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
                json_data: {
                    ajax: {
                        url: Routing.generate('pim_enrich_categorytree_children', {_format: 'json'}),
                        data: function (node) {
                            if (-1 === node) {
                                return {id: this.get_container().data('tree-id')};
                            }

                            return {id: node.attr('id').replace('node_', '')};
                        }
                    }
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

            loadingNode: 0,

            /**
             * Overrides the constructor in order to bind some events
             */
            initialize: function () {
                _.extend(this.loadingNode, Backbone.Events);

                this.listenTo(this, 'increase_loading', function () {
                    if (0 === this.loadingNode) {
                        var loadingMask = new LoadingMask();
                        loadingMask.render().$el.appendTo(this.$el.parent());
                        loadingMask.show();
                    }

                    this.loadingNode = this.loadingNode + 1;
                });

                this.listenTo(this, 'decrease_loading', function () {
                    if (1 === this.loadingNode) {
                        this.$el.parent().find('.loading-mask').remove();
                    }

                    this.loadingNode = this.loadingNode - 1;
                });
            },

            /**
             * Callback called when a node is checked in jstree
             *
             * @param {Object} data
             */
            checkNode: function (data) {
                var node = data.rslt.obj;
                var nodeId = node[0].id.replace('node_', '');
                // All products case
                if ('all' === nodeId) {
                    // Uncheck other nodes
                    data.inst.get_container_ul().find('li.jstree-checked:not(.jstree-all)').each(function () {
                        data.inst.uncheck_node(this);
                    });

                    this.model.clear();
                } else {
                    this.model.include(nodeId);

                    // Open the node and check children
                    data.inst.open_node(node, function () {
                        $('#' + node[0].id + ' > ul > li').each(function () {
                            data.inst.check_node(this);
                        });
                    });

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
                var nodeId = data.rslt.obj[0].id.replace('node_', '');

                if ('all' !== nodeId) {
                    this.model.exclude(nodeId);
                }
            },

            /**
             * Callback called when a node is loaded in jstree
             *
             * @param {Object} data
             */
            loadNode: function (data) {
                this.trigger('increase_loading');

                var self = this;
                var node = data.rslt.obj;
                var childrens;

                if (-1 === node) {
                    childrens = data.inst.get_container().find('> ul > li');

                    // Add the All products checkbox
                    data.inst.create_node(data.inst.get_container(), 'last', {
                        attr: {
                            'id': 'node_all',
                            'class': 'jstree-unclassified jstree-all separated',
                            'data-code': 'all'
                        },
                        data: { title: __('jstree.all') }
                    }, function ($node) {
                        if (0 === this.model.get('included').length) {
                            data.inst.check_node($node);
                        }
                    }.bind(this), true);
                } else {
                    childrens = $('#node_' + node[0].id.replace('node_', '') + '> ul > li');
                }

                // Load all children recursively
                childrens.each(function () {
                    if (!data.inst.is_leaf(this)) {
                        self.trigger('increase_loading');
                        data.inst.load_node(this, function () {
                            self.trigger('decrease_loading');
                        }, $.noop);
                    }

                    if (self.model.get('included').includes(this.id.replace('node_', '').toString())) {
                        data.inst.check_node(this);
                    }
                });

                this.trigger('decrease_loading');
            },

            /**
             * Render the tree in the element's HTML when the channel category is fetched and bind events from jstree
             */
            render: function () {
                this.trigger('increase_loading');

                FetcherRegistry.initialize().then(function () {
                    FetcherRegistry.getFetcher('channel')
                        .fetch(this.attributes.channel)
                        .then(function (channel) {
                            this.$el.html(this.template({tree: channel.category}));
                            this.$('.root').jstree(this.config)
                                .on('check_node.jstree', function (event, data) {
                                    this.checkNode(data);
                                }.bind(this))
                                .on('uncheck_node.jstree', function (event, data) {
                                    this.uncheckNode(data);
                                }.bind(this))
                                .on('load_node.jstree', function (event, data) {
                                    this.loadNode(data);
                                }.bind(this));
                        }.bind(this))
                        .done(function () {
                            this.trigger('decrease_loading');
                        }.bind(this));
                }.bind(this));
            }
        });
    }
);
