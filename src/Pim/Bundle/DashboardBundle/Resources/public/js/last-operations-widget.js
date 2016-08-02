define(
    [
        'jquery',
        'underscore',
        'routing',
        'oro/navigation',
        'pim/dashboard/abstract-widget',
        'text!pim/dashboard/template/last-operations-widget'
    ],
    function ($, _, Routing, Navigation, AbstractWidget, template) {
        'use strict';

        return AbstractWidget.extend({
            tagName: 'table',

            id: 'last-operations-widget',

            className: 'table table-condensed table-light groups unspaced',

            labelClasses: {
                1: 'success',
                3: 'info',
                4: 'important',
                5: 'important',
                6: 'important',
                7: 'important',
                8: 'inverse'
            },

            options: {
                contentLoaded: false
            },

            template: _.template(template),

            events: {
                'click a.btn': 'followLink',
                'click a#btn-show-list': 'showList'
            },

            followLink: function (e) {
                e.preventDefault();
                var route;
                var operationType = $(e.currentTarget).data('operation-type');

                switch (operationType) {
                    case 'mass_edit':
                    case 'quick_export':
                        route = Routing.generate(
                            'pim_enrich_job_tracker_show',
                            { id: $(e.currentTarget).data('id') }
                        );
                        break;
                    default:
                        route = Routing.generate(
                            'pim_importexport_' + operationType + '_execution_show',
                            { id: $(e.currentTarget).data('id') }
                        );
                        break;
                }

                Navigation.getInstance().setLocation(route);
            },

            showList: function (e) {
                e.preventDefault();

                Navigation.getInstance().setLocation(Routing.generate('pim_enrich_job_tracker_index'));
            },

            _processResponse: function (data) {
                this.options.contentLoaded = true;

                _.each(data, function (operation) {
                    operation.labelClass = this.labelClasses[operation.status] ?
                        'label-' + this.labelClasses[operation.status]
                        : '';
                    operation.statusLabel = operation.statusLabel.slice(0, 1).toUpperCase() +
                        operation.statusLabel.slice(1).toLowerCase();
                }, this);

                return data;
            }
        });
    }
);
