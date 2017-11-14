'use strict';

define([
        'jquery',
        'pim/controller/form',
        'pim/security-context',
        'pim/form-config-provider',
        'pim/router'
    ], function (
        $,
        FormController,
        securityContext,
        configProvider,
        router
    ) {
        return FormController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route, path) {
                return securityContext.initialize().then(() => {
                    if (!securityContext.isGranted('pim_user_role_edit')) {
                        router.redirectToRoute('pim_dashboard_index');

                        return;
                    }

                    return $.get(path)
                        .then(this.renderTemplate.bind(this))
                        .promise();
                })
            },

            /**
             * {@inheritdoc}
             */
            afterSubmit: function () {
                securityContext.initialize();
                configProvider.clear();

                FormController.prototype.afterSubmit.apply(this, arguments);

                if (!this.$('#entity-updated span').is(':visible')) {
                    location.reload();
                }
            }
        });
    }
);
