'use strict';
/**
 * Launch button
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
                var link = $(event.currentTarget);
                var stepIndex = link.data('step-index');
                var warningIndex = link.data('warning-index');
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
                    failures: model.failures,
                    id: model.meta.id
                }));

                return this;
            }
        });
    }
);
