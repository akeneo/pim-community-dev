'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'text!pimee/template/product/meta/published'
    ],
    function (_, BaseForm, mediator, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            className: 'published-version',
            template: _.template(formTemplate),
            configure: function () {
                this.listenTo(mediator, 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var product = this.getFormData();

                this.$el.html(
                    this.template({
                        isPublished: product.meta.published,
                        label: _.__('pimee_enrich.entity.product.meta.published'),
                        publishedVersion: this.getPublishedVersion(product)
                    })
                );

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
