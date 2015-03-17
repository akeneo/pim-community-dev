'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/panel/completeness'
    ],
    function(_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'tab-pane',
            id: 'product-categories',
            configure: function () {
                this.getRoot().addTab('categories', 'Categories');

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                // this.$el.html(
                //     this.template({
                //         state: this.getRoot().state.toJSON()
                //     })
                // );

                // this.$el.appendTo(this.getRoot().$('#form-tab-content'));

                // if (this.getRoot().state.get('currentTab') === 'categories') {
                //     this.$el.addClass('active');
                // } else {
                //     this.$el.removeClass('active');
                // }

                // new TreeAssociate('#trees', '#hidden-tree-input');

                return this;
            }
        });
    }
);
