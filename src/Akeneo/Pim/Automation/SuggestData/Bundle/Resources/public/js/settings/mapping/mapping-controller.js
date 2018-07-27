'use strict';

/**
 * Mapping controller. Allows to show an empty page if connection is not activated.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
define(
    [
        'underscore',
        'pim/controller/front',
        'pim/form-builder',
        'pimee/fetcher/pim-ai-connection'
    ],
    function (_, BaseController, FormBuilder, ConnectionFetcher) {
        return BaseController.extend({
            initialize: function (options) {
                this.options = options;
            },

            /**
             * {@inheritdoc}
             */
            renderForm: function () {
                return ConnectionFetcher.isConnectionActivated(this.options.config.connectionCode)
                    .then(connectionIsActivated => {
                        const entity = this.options.config.entity;
                        let formToBuild = 'pimee-' + entity + '-index-inactive-connection';

                        if (connectionIsActivated) {
                            formToBuild = 'pimee-' + entity + '-index';
                        }

                        return FormBuilder.build(formToBuild)
                            .then((form) => {
                                form.setElement(this.$el).render();

                                return form;
                            });
                    });
            }
        });
    }
);
