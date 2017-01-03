define(
    [
        'jquery',
        'underscore',
        'routing',
        'oro/navigation',
        'pim/dashboard/abstract-widget',
        'text!pim/dashboard/template/last-operations-widget',
        'text!pim/dashboard/template/view-all-btn'
    ],
    function ($, _, Routing, Navigation, AbstractWidget, template, viewAllBtnTemplate) {
        'use strict';

        return AbstractWidget.extend({
            labelClasses: {
                1: 'success',
                3: 'info',
                4: 'important',
                5: 'important',
                6: 'important',
                7: 'important',
                8: 'inverse'
            },

            viewAllTitle: 'Show job tracker',

            options: {
                contentLoaded: false
            },

            template: _.template(template),

            jobTrackerBtnTemplate: _.template(viewAllBtnTemplate),

            events: {
                'click .show-details-btn': 'showOperationDetails'
            },

            /**
             * Redirect to the clicked operation page
             *
             * @param {Object} event
             */
            showOperationDetails: function (event) {
                event.preventDefault();
                var route;
                var operationType = $(event.currentTarget).data('operation-type');

                switch (operationType) {
                    case 'mass_edit':
                    case 'quick_export':
                        route = Routing.generate(
                            'pim_enrich_job_tracker_show',
                            { id: $(event.currentTarget).data('id') }
                        );
                        break;
                    default:
                        route = Routing.generate(
                            'pim_importexport_' + operationType + '_execution_show',
                            { id: $(event.currentTarget).data('id') }
                        );
                        break;
                }

                Navigation.getInstance().setLocation(route);
            },

            /**
             * Call when user clicks on the show job tracker button. Redirect to the Job tracker.
             *
             * @param {Object} event
             */
            showTracker: function (event) {
                event.preventDefault();

                Navigation.getInstance().setLocation(Routing.generate('pim_enrich_job_tracker_index'));
            },

            /**
             * {@inheritdoc}
             */
            _afterLoad: function () {
                AbstractWidget.prototype._afterLoad.apply(this, arguments);

                var $btn = this._getViewAllBtn();

                if (!_.isEmpty(this.data)) {
                    this._addShowTrackerBtn();
                } else if (0 > $btn.length) {
                    $btn.hide();
                }
            },

            /**
             * Add the button which show the job tracker
             */
            _addShowTrackerBtn: function () {
                var $btn = this._getViewAllBtn();

                if (0 < $btn.length) {
                    return;
                }

                var $jobTrackerBtn = $(this.jobTrackerBtnTemplate({ title: this.viewAllTitle }));

                this.$el.parent().siblings('.widget-header').append($jobTrackerBtn);
                $jobTrackerBtn.on('click', this.showTracker.bind(this));
            },

            /**
             * {@inheritdoc}
             */
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
            },

            /**
             * Returns the view all button
             *
             * @return {jQuery}
             */
            _getViewAllBtn: function () {
                return $('.view-all-btn[title="' + this.viewAllTitle + '"]');
            }
        });
    }
);
