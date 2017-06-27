/**
 * Group type select2 to be added in a creation form
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'underscore',
    'backbone',
    'routing',
    'pim/form',
    'pim/user-context',
    'pim/i18n',
    'oro/translator',
    'pim/template/form/creation/job'
], function(
        $,
        _,
        Backbone,
        Routing,
        BaseForm,
        UserContext,
        i18n,
        __,
        template
    ) {

    return BaseForm.extend({
        options: {},
        template: _.template(template),
        events: {
            'change input': 'updateModel'
        },

        /**
             * Configure the form
             *
             * @return {Promise}
             */
        configure() {
            return $.when(BaseForm.prototype.configure.apply(this, arguments));
        },

        /**
         * Model update callback
         */
        updateModel(event) {
            this.getFormModel().set('jobName', event.target.value);
        },

        fetchJobs() {
            const url = Routing.generate('pim_enrich_job_instance_rest_jobs_get');

            return $.ajax({
                url,
                type: 'GET',
                data: { jobType: 'import' },
                cache: true
            }).done(jobs => {
                this.jobs = jobs;
                this.renderJobs();
            });
        },

        renderJobs() {
            this.$el.html(this.template({
                label: __('pim_enrich.form.group.tab.properties.type'),
                jobs: this.jobs,
                required: __('pim_enrich.form.required'),
                __
            }));
        },

        /**
             * Renders the form
             *
             * @return {Promise}
             */
        render() {
            if (!this.configured) return this;

            if (!this.jobs) {
                this.fetchJobs();
            } else {
                this.renderJobs();
            }
        }
    });
});
