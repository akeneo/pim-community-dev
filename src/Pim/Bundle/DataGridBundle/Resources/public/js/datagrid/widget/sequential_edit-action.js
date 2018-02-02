define(
    ['jquery', 'underscore', 'backbone', 'oro/navigation',  'routing','oro/messenger', 'oro/datagrid/mass-action'],
    function ($, _, Backbone, Navigation, Routing, messenger, MassAction) {
        'use strict';

        return MassAction.extend({
            initialize: function(options) {
                MassAction.prototype.initialize.apply(this, arguments);
                this.route_parameters = { gridName: this.datagrid.name, actionName: this.name };
            },

            execute: function() {
                $.post(this.getLinkWithParameters(), {itemIds: this.getSelectedRows().join(',')})
                    .done(function () {
                        var navigation = Navigation.getInstance(),
                            // url = Routing.generate('pim_enrich_mass_edit_action_sequential_edit_redirect');
                            url = '/enrich/sequential_edit/redirect';

                        navigation.processRedirect({
                            fullRedirect: false,
                            location: url
                        });
                    })
                    .error(function (jqXHR) {
                        messenger.notificationFlashMessage(
                            'error',
                            _.__(jqXHR.responseText)
                        );
                    });
            }
        });
    }
);
