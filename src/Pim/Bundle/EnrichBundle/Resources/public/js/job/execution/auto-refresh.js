'use strict';
/**
 * Auto refresh
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/job-execution/auto-refresh',
        'backbone'
    ],
    function ($, _, __, BaseForm, FetcherRegistry, template, Backbone) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click a': 'startAutoUpdateOnClick'
            },
            _autoRefreshDelay: 1000, //1 second
            _autoRefreshTimeout: null,

            _stopAutoRefreshDelay: 2 * 60 * 1000, //2 minutes
            _stopAutoUpdateTimeout: null,

            _object: null, //Store the last object that has been fetch

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim-job-execution-form:start-auto-update',
                    this.startAutoUpdate);

                // Clear interval/timeout when changing the page
                Backbone.Router.prototype.on('route', this.stopAll.bind(this));
            },

            /**
             * Restart the auto refresh timeout
             */
            restartAutoRefreshTimeout: function (object) {
                this._autoRefreshTimeout =
                    setTimeout(this.fetchData.bind(this, object), this._autoRefreshDelay);
            },

            /**
             * Start the auto update
             */
            startAutoUpdate: function (object) {
                this.$('img').toggleClass('transparent', !object.isRunning);
                this.$('a').addClass('transparent');

                //Refreshing data every seconds
                this.restartAutoRefreshTimeout(object);

                //After 2 minutes, stop the auto refresh and display the button 'Refresh' (only if the job is not done!)
                this._stopAutoUpdateTimeout =
                    setTimeout(this.stopAutoUpdate.bind(this), this._stopAutoRefreshDelay);
            },

            /**
             * Fetch the data
             * @param jobExecution
             */
            fetchData: function (jobExecution) {

                if (jobExecution && jobExecution.meta && jobExecution.meta.jobId) {

                    if (jobExecution.isRunning) {
                        this.$('img').removeClass('transparent');
                        var jobId = jobExecution.meta.jobId;
                        FetcherRegistry.getFetcher('job-execution').fetch(jobId, {id: jobId, cached: false})
                            .then(function (newJobExecution) {
                                this._object = newJobExecution;
                                this.getRoot().trigger('pim-job-execution-form:newData', newJobExecution);
                                this.restartAutoRefreshTimeout(newJobExecution);
                            }.bind(this));
                    } else {
                        ///Data are up to date!
                        this.stopAll();
                    }

                } else {
                    throw new Error('In auto-refresh/fetchData , jobExecution.meta.id should exist');
                }
            },

            /**
             * Called when clicking on 'Refresh' button
             * @param event
             */
            startAutoUpdateOnClick: function (event) {
                event.preventDefault();
                this.startAutoUpdate(this._object);
            },

            /**
             * Stop all timeout and display the refresh button
             */
            stopAutoUpdate: function () {
                clearTimeout(this._autoRefreshTimeout);
                clearTimeout(this._stopAutoUpdateTimeout);
                this.$('img').addClass('transparent');
                this.$('a').removeClass('transparent');
            },

            /**
             * Stop all timeout and hide loader and refresh button
             */
            stopAll: function () {
                clearTimeout(this._autoRefreshTimeout);
                clearTimeout(this._stopAutoUpdateTimeout);
                this.$('img').addClass('transparent');
                this.$('a').addClass('transparent');
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    __: __
                }));

                return this;
            }
        });
    }
);
