'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/tab/categories',
        'pim/tree/associate'
    ],
    function(_, Backbone, BaseForm, formTemplate, TreeAssociate) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tab-pane',
            id: 'product-categories',
            events: {
                'click .nav-tabs li': 'changeTree',
                'change #hidden-tree-input': 'updateModel'
            },
            treeAssociate: null,
            initialize: function () {
                this.state = new Backbone.Model();

                this.state.set('selectedCategories', [116, 128]);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('categories', 'Categories');

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.$el.appendTo(this.getRoot().$('.form-container .tab-pane[data-tab="categories"]'));

                if (this.treeAssociate) {
                    this.delegateEvents();
                    return;
                }

                this.$el.html(
                    this.template({
                        product: this.getData(),
                        locale: this.getRoot().state.get('locale'),
                        state: this.state.toJSON(),
                        trees: this.loadTrees()
                    })
                );

                this.treeAssociate = new TreeAssociate('#trees', '#hidden-tree-input');

                return this;
            },
            loadTrees: function () {
                return [
                    {id: 1, code: 'master', label: 'master', associated: true},
                    {id: 2, code: 'sales', label: 'sales', associated: false}
                ];
            },
            changeTree: function (event) {
                this.state.set('currentTree', event.currentTarget.dataset.tree);

                this.treeAssociate.switchTree(event.currentTarget.dataset.treeId);
            },
            updateModel: function (event) {
                this.state.set('selectedCategories', event.currentTarget.value.split(','));
            }
        });
    }
);
