'use strict';
/**
 * Download file extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/form/download-files',
        'routing'
    ],
    function (_,
              __,
              BaseForm,
              template,
              Routing) {
        return BaseForm.extend({
            tagName: 'a',
            className: 'AknButtonList',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

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
                var formData = this.getFormData();

                this.$el.html(this.template({
                    __: __,
                    archives: formData.meta.archives,
                    executionId: formData.meta.id,
                    generateRoute: this.getUrl.bind(this)
                }));

                return this;
            },

            /**
             * Get the url from parameters
             * @returns {string}
             */
            getUrl: function (parameters) {
                return Routing.generate(
                    this.config.url,
                    parameters
                );
            }
        });
    }
);
