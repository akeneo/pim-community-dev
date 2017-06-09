'use strict';
/**
 * Set attribute requirements operation
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'routing',
        'pim/mass-edit-form/product/operation',
        'pim/user-context',
        'pim/form-builder',
        'pim/common/property',
        'pim/fetcher-registry',
        'pim/template/mass-edit/family/set-requirements'
    ],
    function (
        _,
        __,
        Routing,
        BaseOperation,
        UserContext,
        FormBuilder,
        propertyAccessor,
        FetcherRegistry,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            formPromise: null,

            render: function () {
                if (null === this.getValue()) {
                    this.setValue([]);
                }

                var family = {
                    attributes: [],
                    attribute_requirements: {},
                    meta: {}
                };
                if (!this.formPromise) {
                    this.formPromise = FormBuilder.build('pim-mass-family-edit-form').then(function (form) {
                        form.setData(family);
                        form.trigger('pim_enrich:form:entity:post_fetch', family);
                        this.listenTo(form, 'pim_enrich:mass_edit:model_updated', this.updateModel);

                        return form;
                    }.bind(this));
                } else {
                }

                this.formPromise.then(function (form) {
                    this.$el.html(this.template({}));
                    form.setElement(this.$('.set-requirements')).render();
                    form.trigger('pim_enrich:form:update_read_only', this.readOnly);
                }.bind(this));

                return this;
            },

            updateModel: function (data) {
                FetcherRegistry.getFetcher('channel').fetchAll().then(function (channels) {
                    var attributeRequirements = [];

                    _.each(data.attributes, function (attributeCode) {
                        _.each(channels, function (channel) {
                            attributeRequirements.push({
                                attribute_code: attributeCode,
                                channel_code: channel.code,
                                is_required: _.contains(propertyAccessor.accessProperty(
                                    data.attribute_requirements,
                                    channel.code,
                                    []
                                ), attributeCode)
                            });
                        });
                    });

                    this.setValue(attributeRequirements);
                }.bind(this));

            },

            setValue: function (values) {
                var data = this.getFormData();

                data.actions = values;

                this.setData(data);
            },

            getValue: function () {
                return this.getFormData().actions;
            }
        });
    }
);
