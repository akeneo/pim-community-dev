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
    ['pim/form', 'pim/user-context', 'pim/i18n', 'underscore'],
    function (BaseForm, UserContext, i18n, _) {
        return BaseForm.extend({
            tagName: 'h1',
            className: 'AknTitleContainer-title',

            /**
             * @param {Object} meta
             */
            initialize: function (meta) {
                this.config = _.extend({}, meta.config);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogScope', this.render);
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

                if (this.config.field) {
                    return data[this.config.field];
                }

                if (undefined === data.labels) {
                    return '';
                }

                return i18n.getLabel(
                    data.labels,
                    UserContext.get('catalogLocale'),
                    data.code
                );
            }
        });
    }
);
