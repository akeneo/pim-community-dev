'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/tab/categories',
        'pim/tree/associate'
    ],
    function(_, BaseForm, formTemplate, TreeAssociate) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tab-pane',
            id: 'product-categories',
            configure: function () {
                this.getRoot().addTab('categories', 'Categories');

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.$el.html(
                    this.template({
                        state: this.getRoot().state.toJSON()
                    })
                );

                this.$el.appendTo(this.getRoot().$('.form-container .tab-pane[data-tab="categories"]'));

                return this;
            }
        });
    }
);
