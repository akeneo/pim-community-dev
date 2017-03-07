'use strict';
/**
 * Download file extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/form/download-file',
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
            tagName: 'a',
            className: 'AknButton AknButton--grey AknButton--withIcon AknTitleContainer-rightButton btn-download',
            template: _.template(template),

            /**
             * @param {Object} meta
             */
            initialize: function (meta) {
                this.config = meta.config;
            },

            render: function () {
                this.$el.html(this.template({
                    btnLabel: __(this.config.label),
                    btnIcon: this.config.iconName
                }));
                this.$el.attr('href', this.getUrl());

                return this;
            },

            getUrl: function () {
                if (this.config.url) {
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
                        parameters);
                } else {
                    return '';
                }
            }
        });
    }
);
