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
    'pim/fetcher-registry',
    'pim/initselect2',
    'pim/template/form/creation/axis'
], function (
    $,
    _,
    Backbone,
    Routing,
    BaseForm,
    UserContext,
    i18n,
    __,
    FetcherRegistry,
    initSelect2,
    template
) {
    return BaseForm.extend({
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
            return $.when(
                FetcherRegistry.initialize(),
                BaseForm.prototype.configure.apply(this, arguments)
            );
        },

        /**
             * Model update callback
             */
        updateModel(event) {
            const axes = event.target.value.split(',');
            this.getFormModel().set('axes', axes);
        },

        formatAxes(axes, useTranslation) {
            const locale = UserContext.get('catalogLocale');
            const formatted = [];

            axes.forEach(axis => {
                const id = axis.code;
                let text = axis.label;

                if (useTranslation) {
                    text = i18n.getLabel(axis.labels, locale, id);
                }

                formatted.push({ id, text });
            });

            return formatted;
        },


        /**
             * Parses each group type for the select display
             *
             * @param  {Array} types The search results
             * @return {Object}
             */
        parseResults(axes) {
            return { results: this.formatAxes(axes) };
        },

        fetchAxes(element, callback) {
            const axes = this.getFormData().axes;
            if (!axes) return;

            FetcherRegistry.getFetcher('attribute')
            .fetchByIdentifiers(axes)
            .then(fetchedAxes => callback(this.formatAxes(fetchedAxes, true)));
        },

        /**
         * Renders the form
         *
         * @return {Promise}
         */
        render() {
            if (!this.configured) return this;

            const locale = UserContext.get('catalogLocale');
            const errors = this.getRoot().validationErrors || [];
            const identifier = this.options.config.identifier || 'axis';

            this.$el.html(this.template({
                label: 'Axis',
                required: __('pim_enrich.form.required'),
                help: __('pim_enrich.form.variant_group.axis.help'),
                errors: errors.filter(error => {
                    return error.message.includes('axis');
                })
            }));

            this.delegateEvents();

            var options = {
                allowClear: true,
                multiple: true,
                initSelection: this.fetchAxes.bind(this),
                ajax: {
                    url: Routing.generate('pim_enrich_attribute_axes_index'),
                    results: this.parseResults.bind(this),
                    quietMillis: 250,
                    cache: true,
                    data() {
                        return { locale };
                    }
                }
            };

            initSelect2.init(this.$('input'), options).select2('val', []);
            this.$('[data-toggle="tooltip"]').tooltip();
        }
    });
});
