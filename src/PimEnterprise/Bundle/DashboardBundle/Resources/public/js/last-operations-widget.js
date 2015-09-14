define(
    ['jquery', 'underscore', 'routing', 'oro/navigation', 'pim/dashboard/last-operations-widget'],
    function ($, _, Routing, Navigation, baseWidget) {
        'use strict';

        return baseWidget.extend({
            followLink: function (e) {
                e.preventDefault();
                var route;
                var operationType = $(e.currentTarget).data('operation-type');

                switch (operationType) {
                    case 'mass_edit':
                    case 'mass_upload':
                    case 'quick_export':
                        route = Routing.generate(
                            'pim_enrich_job_tracker_show',
                            {id: $(e.currentTarget).data('id')}
                        );
                        break;
                    default:
                        route = Routing.generate(
                            'pim_importexport_' + operationType + '_execution_show',
                            {id: $(e.currentTarget).data('id')}
                        );
                        break;
                }

                Navigation.getInstance().setLocation(route);
            }
        });
    }
);
