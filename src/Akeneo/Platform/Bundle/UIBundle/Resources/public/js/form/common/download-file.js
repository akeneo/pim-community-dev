'use strict';
/**
 * Download file extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/form/download-file',
        'routing',
        'pim/user-context',
        'pim/common/property'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        Routing,
        UserContext,
        propertyAccessor
    ) {
        return BaseForm.extend({
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
                if (!this.isVisible()) {
                    return this;
                }
                this.$el.html(this.template({
                    btnLabel: __(this.config.label),
                    btnIcon: this.config.iconName,
                    url: this.getUrl()
                }));

                return this;
            },

            /**
             * Get the url with parameters
             *
             * @returns {string}
             */
            getUrl: function () {
                var parameters = {};
                if (this.config.urlParams) {
                    var formData = this.getFormData();
                    this.config.urlParams.forEach(function (urlParam) {
                        parameters[urlParam.property] =
                            propertyAccessor.accessProperty(formData, urlParam.path);
                    });
                }

                return Routing.generate(
                    this.config.url,
                    parameters
                );
            },

            /**
             * Returns true if the extension should be visible
             *
             * @returns {boolean}
             */
            isVisible: function () {
                return propertyAccessor.accessProperty(this.getFormData(), this.config.isVisiblePath);
            }
        });
    }
);
