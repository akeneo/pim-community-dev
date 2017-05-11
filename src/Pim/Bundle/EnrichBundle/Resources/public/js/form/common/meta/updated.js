'use strict';
/**
 * Updated at extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'oro/mediator',
        'pim/template/form/meta/updated'
    ],
    function (_, __, BaseForm, mediator, formTemplate) {
        return BaseForm.extend({
            tagName: 'span',
            className: 'AknTitleContainer-metaItem',
            template: _.template(formTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                this.label   = __(this.config.label);
                this.labelBy = __(this.config.labelBy);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var product = this.getFormData();
                var html = '';

                if (product.meta.updated) {
                    html = this.template({
                        label: this.label,
                        labelBy: this.labelBy,
                        loggedAt: _.result(product.meta.updated, 'logged_at', null),
                        author: _.result(product.meta.updated, 'author', null)
                    });
                }

                this.$el.html(html);

                return this;
            }
        });
    }
);
