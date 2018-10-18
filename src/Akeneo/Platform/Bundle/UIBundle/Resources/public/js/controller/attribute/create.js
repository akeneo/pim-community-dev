/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'pim/controller/front',
    'pim/form-builder',
    'pim/fetcher-registry'
],
function (_, BaseController, FormBuilder, fetcherRegistry) {
    return BaseController.extend({
        /**
         * {@inheritdoc}
         */
        renderForm: function () {
            if (!this.active) {
                return;
            }

            fetcherRegistry.getFetcher('attribute-group').clear();
            fetcherRegistry.getFetcher('locale').clear();
            fetcherRegistry.getFetcher('measure').clear();

            var type = this.getQueryParam(location.href, 'attribute_type');

            return FormBuilder.getFormMeta('pim-attribute-create-form')
                .then(FormBuilder.buildForm)
                .then((form) => {
                    form.setType(type);

                    return form.configure().then(() => {
                        return form;
                    });
                })
                .then((form) => {
                    this.on('pim:controller:can-leave', (event) => {
                        form.trigger('pim_enrich:form:can-leave', event);
                    });

                    form.setData(this.getNewAttribute(type));

                    form.setElement(this.$el).render();

                    return form;
                });
        },

        /**
         * Extracts the value of a given parameter from the query string.
         *
         * @param {String} url
         * @param {String} paramName
         *
         * @return  {String}
         */
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
        },

        /**
         * @param {String} type
         *
         * @return {Object}
         */
        getNewAttribute: function (type) {
            return {
                code: '',
                labels: {},
                type: type,
                available_locales: []
            };
        }
    });
});
