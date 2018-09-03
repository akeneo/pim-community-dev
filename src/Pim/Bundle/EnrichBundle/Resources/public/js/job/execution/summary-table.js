'use strict';
/**
 * Displays the summary table for a job execution
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
        'pim/template/job-execution/summary-table'
    ],
    function ($, _, __, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click .data': 'toggleDisplayWarning'
            },

            /**
             * Display or hide a warning details
             * @param event
             */
            toggleDisplayWarning: function (event) {
                var stepIndex = event.currentTarget.dataset.stepIndex;
                var warningIndex = event.currentTarget.dataset.warningIndex;
                var model = this.getFormData();
                model.stepExecutions[stepIndex].warnings[warningIndex].expanded =
                    !model.stepExecutions[stepIndex].warnings[warningIndex].expanded;

                this.render();
            },

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
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var model = this.getFormData();
                this.$el.html(this.template({
                    transAndUpperCase: function (str) {
                        return __(str).toUpperCase();
                    },
                    __: __,
                    stepExecutions: model.stepExecutions,
                    status: model.status,
                    failures: model.failures,
                    id: model.meta.id,
                    translateStepExecutionLabel: this.translateStepExecutionLabel
                }));

                return this;
            },

            /**
             * Get the translation of a stepExecution.
             * If the translation exists for this specific job, returns it, else returns the default one.
             *
             * @param stepExecution
             * @returns {string}
             */
            translateStepExecutionLabel: function(stepExecution) {
                let key = 'batch_jobs.' + stepExecution.job + '.' + stepExecution.label + '.label';
                if (__(key) === key) {
                    key = 'batch_jobs.default_steps.' + stepExecution.label;
                }

                return __(key);
            }
        });
    }
);
