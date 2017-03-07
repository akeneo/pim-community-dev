'use strict';
/**
 * Launch button
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
        'routing',
        'oro/navigation',
        'pim/common/property',
        'oro/messenger',
        'text!pim/template/job-execution/summary-table'
    ],
    function ($, _, __, BaseForm, Routing, Navigation, propertyAccessor, messenger, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click a.data': 'toggleDisplayWarning'
            },

            /**
             * Display or hide a warning details
             * @param event
             */
            toggleDisplayWarning: function (event) {
                event.preventDefault();
                var link = event.currentTarget;
                var table = link.nextElementSibling;
                table.classList.toggle('hide');
                link.textContent = link.textContent.trim() === link.getAttribute('data-hide-label') ?
                    link.getAttribute('data-show-label') : link.getAttribute('data-hide-label');
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritDoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim-job-execution-form:newData',
                    function (newData) {
                        this.setData(newData);
                        this.render();
                    });
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
                    failures: model.failures,
                    jobId: model.meta.jobId
                }));

                return this;
            }
        });
    }
);
