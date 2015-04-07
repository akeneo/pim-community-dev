'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/tab/categories',
        'routing',
        'pim/tree/associate'
    ],
    function(_, Backbone, BaseForm, formTemplate, Routing, TreeAssociate) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tab-pane active',
            id: 'product-categories',
            events: {
                'click .nav-tabs li': 'changeTree',
                'change #hidden-tree-input': 'updateModel'
            },
            treeAssociate: null,
            cache: {},
            initialize: function () {
                this.state = new Backbone.Model();

                this.state.set('selectedCategories', []);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('categories', 'Categories');

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.loadTrees().done(_.bind(function (trees) {
                    this.$el.html(
                        this.template({
                            product: this.getData(),
                            locale: 'en_US',
                            state: this.state.toJSON(),
                            trees: trees
                        })
                    );

                    this.treeAssociate = new TreeAssociate('#trees', '#hidden-tree-input');
                    this.delegateEvents();
                }, this));

                return this;
            },
            loadTrees: function () {
                var promise = $.Deferred();

                $.getJSON(
                    Routing.generate('pim_enrich_product_category_rest_list', {id: this.getData().meta.id })
                ).done(_.bind(function(data) {
                    _.each(data.categories, _.bind(function(category) {
                        this.cache[category.id] = category.code;
                    }, this));
                    if (_.isEmpty(this.state.get('selectedCategories'))) {
                        this.state.set('selectedCategories', _.pluck(data.categories, 'id'));
                    }
                    promise.resolve(data.trees);
                }, this));

                return promise.promise();
            },
            changeTree: function (event) {
                this.state.set('currentTree', event.currentTarget.dataset.tree);

                this.treeAssociate.switchTree(event.currentTarget.dataset.treeId);
            },
            updateModel: function (event) {
                var selectedIds = _.filter(event.currentTarget.value.split(','), _.identity);
                this.state.set('selectedCategories', selectedIds);
                var categoryCodes = _.map(selectedIds, _.bind(this.getCategoryCode, this));

                this.getRoot().model.set('categories', categoryCodes);
            },
            getCategoryCode: function (id) {
                if (!this.cache[id]) {
                    this.cache[id] = this.$('#node_' + id).data('code');
                }

                return this.cache[id];
            }
        });
    }
);
