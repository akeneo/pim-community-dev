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
        'jquery',
        'underscore',
        'oro/translator',
        'oro/messenger',
        'routing',
        'pim/mass-edit-form/product/operation',
        'pim/user-context',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/i18n',
        'pim/common/property',
        'pim/template/mass-edit/product/edit-common-attributes'
    ],
    function (
        $,
        _,
        __,
        messenger,
        Routing,
        BaseOperation,
        UserContext,
        FormBuilder,
        FetcherRegistry,
        i18n,
        propertyAccessor,
        template
    ) {
        return BaseOperation.extend({
            className: 'AknGridContainer--withoutNoDataPanel',
            template: _.template(template),
            errors: null,
            formPromise: null,
            channel: null,
            locales: [],

            /**
             * {@inheritdoc}
             */
            configure: function () {
                return $.when(
                    FetcherRegistry.getFetcher('channel')
                        .fetch(UserContext.get('catalogScope'), {force_list_method: true}),
                    FetcherRegistry.getFetcher('locale').search({ activated: true, cached: false })
                ).then((channel, locales) => {
                    this.channel = channel;
                    this.locales = locales;
                });
            },

            /**
             * {@inheritdoc}
             */
            reset: function () {
                this.setValue({});
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var product = {
                    meta: {},
                    values: this.getValue()
                };

                if (!this.formPromise) {
                    this.formPromise = FormBuilder.build(this.config.innerForm).then(function (form) {
                        form.setData(product);
                        form.trigger('pim_enrich:form:entity:post_fetch', product);
                        this.listenTo(form, 'pim_enrich:mass_edit:model_updated', this.updateModel);

                        return form;
                    }.bind(this));
                }

                this.formPromise.then(function (form) {
                    this.$el.html(this.template());
                    form.setElement(this.$('.edit-common-attributes')).render();
                    form.trigger('pim_enrich:form:update_read_only', this.readOnly);

                    // This method renders a complete PEF page, we need to remove useless elements manually.
                    this.$el.find('.navigation').remove();
                    this.$el.find('.AknDefault-thirdColumnContainer').remove();

                    this.$el.find('.AknDefault-mainContent')
                        .addClass('AknDefault-mainContent--withoutPadding')
                        .css({'overflow-x': 'hidden'})
                    ;

                    if (this.errors) {
                        const event = {
                            sentData: product,
                            response: {values: this.errors}
                        };

                        form.trigger('pim_enrich:form:entity:bad_request', event);
                    }
                }.bind(this));

                return this;
            },

            /**
             * Update the mass edit model
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                this.setValue(event.values);
            },

            /**
             * {@inheritdoc}
             */
            getDescription: function () {
                return __(
                    this.config.description,
                    {
                        locale: _.findWhere(this.locales, {code: UserContext.get('catalogLocale')}).label,
                        scope: i18n.getLabel(
                            this.channel.labels,
                            UserContext.get('catalogLocale'),
                            this.channel.code
                        )
                    }
                );
            },

            /**
             * Update the model after dom event triggered
             *
             * @param {string} group
             */
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

            /**
             * Get current value from mass edit model
             *
             * @return {string}
             */
            getValue: function () {
                var action = _.first(this.getFormData().actions);

                return action ? action.normalized_values : null;
            },

            /**
             * Validate the model before confirmation
             *
             * @return {Promise}
             */
            validate: function () {
                const data = this.getFormData();
                const actions = propertyAccessor.accessProperty(data, 'actions.0.normalized_values', {});

                if (0 === Object.keys(actions).length) {
                    messenger.notify('error', __('pim_enrich.mass_edit.product.operation.edit_common.no_update'));

                    return $.Deferred().resolve(false);
                } else {
                    return $.ajax({
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(this.getValue()),
                        url: Routing.generate('pim_enrich_value_rest_validate')
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
            }
        });
    }
);
