'use strict';
/**
 * Auto refresh
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/template/job-execution/auto-refresh',
        'backbone'
    ],
    function ($, _, __, BaseForm, FetcherRegistry, template, Backbone) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click a': 'startAutoUpdateOnClick'
            },
            autoRefreshDelay: 1000, //1 second
            autoRefreshTimeout: null,

            stopAutoRefreshDelay: 2 * 60 * 1000, //2 minutes
            stopAutoUpdateTimeout: null,

            status: null, //3 status: isLoading | isFinished | isNotFinished

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;
                this.setStatus('isLoading');

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim-job-execution-form:start-auto-update', this.startAutoUpdate);
                this.listenTo(this.getRoot(), 'pim-job-execution-form:stop-auto-update', this.stopAll);

                // Clear interval/timeout when changing the page
                Backbone.Router.prototype.on('route', this.stopAll.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Restart the auto refresh timeout
             */
            restartAutoRefreshTimeout: function () {
                //We do not want a setInterval here,
                //in order to avoid to fetch every second even if a fetch is already in progress...
                clearTimeout(this.autoRefreshTimeout);
                this.autoRefreshTimeout =
                    setTimeout(this.fetchData.bind(this, this.getFormData()), this.autoRefreshDelay);
            },

            /**
             * Start the auto update
             */
            startAutoUpdate: function () {

                //Refreshing data every seconds
                this.restartAutoRefreshTimeout();

                //After 2 minutes, stop the auto refresh and display the button 'Refresh' (only if the job is not done!)
                clearTimeout(this.stopAutoUpdateTimeout);
                this.stopAutoUpdateTimeout =
                    setTimeout(function () {
                        this.stopAll();
                        this.setStatus('isNotFinished');
                    }.bind(this), this.stopAutoRefreshDelay);
            },

            /**
             * Fetch the data
             * @param jobExecution
             */
            fetchData: function (jobExecution) {

                if (jobExecution.isRunning) {
                    this.setStatus('isLoading');
                    var jobId = jobExecution.meta.id;
                    FetcherRegistry.getFetcher('job-execution').fetch(jobId, {id: jobId, cached: false})
                        .then(function (newJobExecution) {
                            this.setData(newJobExecution);
                            this.render();
                            this.restartAutoRefreshTimeout();
                        }.bind(this));
                } else {
                    ///Data are up to date!
                    this.stopAll();
                    this.setStatus('isFinished');
                }
            },

            /**
             * Called when clicking on 'Refresh' button
             */
            startAutoUpdateOnClick: function () {
                this.setStatus('isLoading');
                this.startAutoUpdate();
            },

            /**
             * Stop all timeout
             */
            stopAll: function () {
                clearTimeout(this.autoRefreshTimeout);
                clearTimeout(this.stopAutoUpdateTimeout);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    __: __,
                    status: this.status,
                    loadingShown: this.status === 'isLoading',
                    refreshBtnShown: this.status === 'isNotFinished'
                }));

                return this;
            },

            /**
             * Change the status of the extension
             * @param status (isLoading | isFinished | isNotFinished)
             */
            setStatus: function (status) {
                if (status !== 'isLoading' && status !== 'isFinished' && status !== 'isNotFinished') {
                    throw new Error('Status equal isLoading | isFinished | isNotFinished but === [' + status + ']');
                }
                this.status = status;
                this.render();
            }
        });
    }
);
