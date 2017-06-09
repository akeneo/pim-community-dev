'use strict';
/**
 * Edit common attributes operation
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
        'pim/template/mass-edit/product/edit-common-attributes',
    ],
    function (
        _,
        __,
        Routing,
        BaseOperation,
        UserContext,
        FormBuilder,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),
            errors: null,
            formPromise: null,

            reset: function () {
                this.setValue({});
            },

            render: function () {
                var product = {
                    meta: {},
                    values: this.getValue()
                };
                if (!this.formPromise) {
                    this.formPromise = FormBuilder.build('pim-mass-product-edit-form').then(function (form) {
                        form.setData(product);
                        form.trigger('pim_enrich:form:entity:post_fetch', product);
                        this.listenTo(form, 'pim_enrich:mass_edit:model_updated', this.updateModel);

                        return form;
                    }.bind(this));
                } else {
                }

                this.formPromise.then(function (form) {
                    this.$el.html(this.template({}));
                    form.setElement(this.$('.edit-common-attributes')).render();
                    form.trigger('pim_enrich:form:update_read_only', this.readOnly);

                    if (this.errors) {
                        var event = {
                            sentData: product,
                            response: {values: this.errors}
                        };

                        form.trigger('pim_enrich:form:entity:bad_request', event);
                    }
                }.bind(this));

                return this;
            },

            updateModel: function (event) {
                this.setValue(event.values);
            },

            getDescription: function () {
                return __(
                    this.config.description,
                    {
                        locale: UserContext.get('catalogLocale'),
                        scope: UserContext.get('catalogScope')
                    }
                );
            },

            setValue: function (values) {
                var data = this.getFormData();

                data.actions = [{
                    normalized_values: values,
                    ui_locale: UserContext.get('uiLocale'),
                    attribute_locale: UserContext.get('catalogLocale'),
                    attribute_channel: UserContext.get('catalogScope')
                }];

                this.setData(data);
            },

            getValue: function () {
                var action = _.first(this.getFormData().actions);

                return action ? action.normalized_values : null;
            },

            validate: function () {
                return $.ajax({
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(this.getValue()),
                    url: Routing.generate('pim_enrich_product_template_rest_validate')
                }).then(function (response) {
                    if (!_.isEmpty(response.values)) {
                        this.errors = response.values;

                        this.render();
                    } else {
                        this.errors = {};
                    }

                    return _.isEmpty(this.errors);
                }.bind(this));
            }
        });
    }
);
