 'use strict';
/**
 * Created at extension
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
        'pim/template/form/meta/created'
    ],
    function (_, __, BaseForm, formTemplate) {
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
            render: function () {
                var product = this.getFormData();
                var html = '';

                if (product.meta.created) {
                    html = this.template({
                        label: this.label,
                        labelBy: this.labelBy,
                        loggedAt: _.result(product.meta.created, 'logged_at', null),
                        author: _.result(product.meta.created, 'author', null)
                    });
                }

                this.$el.html(html);

                return this;
            }
        });
    }
);
