/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'pim/controller/base',
    'pim/form-builder',
    'pim/attribute-edit-form/type-specific-form-registry'
],
function (_, BaseController, FormBuilder, FormRegistry) {
    return BaseController.extend({
        /**
         * {@inheritdoc}
         */
        renderRoute: function () {
            if (!this.active) {
                return;
            }

            var type = this.getQueryParam(location.href, 'attribute_type');

            return FormBuilder.buildForm('pim-attribute-create-form')
                .then(function (form) {
                    form.setAdditionalView(
                        'type-specific',
                        FormRegistry.initialize().getFormName(type, 'create')
                    );

                    return form.configure().then(function () {
                        return form;
                    });
                })
                .then(function (form) {
                    this.on('pim:controller:can-leave', function (event) {
                        form.trigger('pim_enrich:form:can-leave', event);
                    });

                    form.setData({
                        code: '',
                        labels: {},
                        type: type,
                        available_locales: []
                    });

                    form.setElement(this.$el).render();
                }.bind(this));
        },

        getQueryParam: function (url, paramName) {
            var params = url.substr(url.lastIndexOf('?') + 1);
            if (!params) {
                return null;
            }

            var paramsList = params.split('=');
            if (!_.contains(paramsList, paramName)) {
                return null;
            }

            return paramsList[paramsList.indexOf(paramName) + 1];
        }
    });
});
