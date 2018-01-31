define(
    [
        'jquery',
        'underscore',
        'pim/router',
        'pim/dashboard/abstract-widget',
        'pim/dashboard/template/last-operations-widget',
        'pim/dashboard/template/view-all-btn'
    ],
    function ($, _, router, AbstractWidget, template, viewAllBtnTemplate) {
        'use strict';

        return AbstractWidget.extend({
            labelClasses: {
                1: 'AknBadge--success',
                3: '',
                4: 'AknBadge--important',
                5: 'AknBadge--important',
                6: 'AknBadge--important',
                7: 'AknBadge--important',
                8: 'AknBadge--error'
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
                var operationType = $(event.currentTarget).data('operation-type');

                switch (operationType) {
                    case 'import':
                    case 'export':
                        router.redirectToRoute(
                            'pim_importexport_' + operationType + '_execution_show',
                            { id: $(event.currentTarget).data('id') }
                        );
                        break;
                    default:
                        router.redirectToRoute(
                            'pim_enrich_job_tracker_show',
                            { id: $(event.currentTarget).data('id') }
                        );
                        break;
                }
            },

            /**
             * Call when user clicks on the show job tracker button. Redirect to the Job tracker.
             *
             * @param {Object} event
             */
            showTracker: function (event) {
                event.preventDefault();

                router.redirectToRoute('pim_enrich_job_tracker_index');
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

                this.$el.closest('.AknWidget').find('.widget-actions').prepend($jobTrackerBtn);
                $jobTrackerBtn.on('click', this.showTracker.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            _processResponse: function (data) {
                this.options.contentLoaded = true;

                _.each(data, function (operation) {
                    operation.labelClass = this.labelClasses[operation.status] ?
                        this.labelClasses[operation.status]
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
