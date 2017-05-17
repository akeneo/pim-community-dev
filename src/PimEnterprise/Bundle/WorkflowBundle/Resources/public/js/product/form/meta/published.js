'use strict';

define(
    [
        'underscore',
        'pim/form',
        'pimee/template/product/meta/published'
    ],
    function (_, BaseForm, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            className: 'AknTitleContainer-metaItem published-version',
            template: _.template(formTemplate),
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var product = this.getFormData();

                if (product.meta.published) {
                    this.$el.html(
                        this.template({
                            label: _.__('pimee_enrich.entity.product.meta.published'),
                            publishedVersion: this.getPublishedVersion(product)
                        })
                    );
                } else {
                    this.$el.html('');
                }

                return this;
            },

            /**
             * Get the published version number for the given product
             *
             * @param {Object} product
             *
             * @returns {int}
             */
            getPublishedVersion: function (product) {
                return _.result(product.meta.published, 'version', null);
            }
        });

        return FormView;
    }
);
