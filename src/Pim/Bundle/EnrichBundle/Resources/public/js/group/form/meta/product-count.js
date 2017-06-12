'use strict';

/**
 * Group meta extension to display number of products this group contains
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/group/meta/product-count'
    ],
    function (_, __, BaseForm, formTemplate) {
        return BaseForm.extend({
            tagName: 'span',
            template: _.template(formTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var group = this.getFormData();
                var html = '';

                if (_.has(group, 'products')) {
                    html = this.template({
                        label: __(this.config.productCountLabel),
                        productCount: group.products.length
                    });
                }

                this.$el.html(html);

                return this;
            }
        });
    }
);
