'use strict';

import {isConnectionActivated} from 'akeneosuggestdata/js/pim-ai/fetcher/connection-fetcher';

/**
 * Mapping controller. Allows to show an empty page if connection is not activated.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
define(
    [
        'underscore',
        'pim/controller/front',
        'pim/form-builder'
    ],
    function (_, BaseController, FormBuilder) {
        return BaseController.extend({
            initialize: function (options) {
                this.options = options;
            },

            /**
             * {@inheritdoc}
             */
            renderForm: function () {
                return isConnectionActivated(this.options.config.connectionCode)
                    .then(connectionIsActivated => {
                        const entity = this.options.config.entity;
                        let formToBuild = 'pimee-' + entity + '-index-inactive-connection';

                        if (connectionIsActivated) {
                            formToBuild = 'pimee-' + entity + '-index';
                        }

                        return FormBuilder.build(formToBuild)
                            .then((form) => {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            }
        });
    }
);
