'use strict';
/**
 * Label extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/form', 'pim/user-context', 'pim/i18n'],
    function (BaseForm, UserContext, i18n) {
        return BaseForm.extend({
            tagName: 'h1',
            className: 'AknTitleContainer-title',

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.text(
                    this.getLabel()
                );

                return this;
            },

            /**
             * Provide the object label
             *
             * @return {String}
             */
            getLabel: function () {
                var data = this.getFormData();

                return i18n.getLabel(
                    data.labels,
                    UserContext.get('catalogLocale'),
                    data.code
                );
            }
        });
    }
);
