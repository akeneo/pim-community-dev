'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/translator',
        'pim/form-builder',
        'pim/template/catalog-volume/section',
        'require-context'
    ],
    function (
        _,
        BaseForm,
        __,
        FormBuilder,
        template,
        requireContext
    ) {
        return BaseForm.extend({
            className: 'AknCatalogVolume-section',
            template: _.template(template),
            config: {
                warningText: __('catalog_volume.axis.warning'),
                templates: {
                    mean_max: 'pim/template/catalog-volume/mean-max',
                    number: 'pim/template/catalog-volume/number'
                }
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (options) {
                this.config = Object.assign({}, this.config, options.config);

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.empty().html(this.template({
                    title: __(this.config.title),
                    hintTitle: __(this.config.hint.title)
                }));

                this.renderAxes(this.config.axes, this.getRoot().getFormData());
            },

            /**
             * Generates the html for each axis depending on the type, appends the axis to the axis container
             * @param  {Array} axes An array of field names for each axis
             * @param  {Object} data An object containing data for each axis
             */
            renderAxes(axes, data) {
                axes.forEach(name => {
                    const axis = data[name];
                    const type = this.config.templates[axis.type];
                    const template = _.template(requireContext(type));

                    const el = template({
                        icon: name.replace(/[_]/g, '-'),
                        value: axis.value,
                        warning: axis.warning,
                        title: __(`catalog_volume.axis.${name}`),
                        warningText: this.config.warningText
                    });

                    this.$('.AknCatalogVolume-axisContainer').append(el);
                });
            }
        });
    }
);
