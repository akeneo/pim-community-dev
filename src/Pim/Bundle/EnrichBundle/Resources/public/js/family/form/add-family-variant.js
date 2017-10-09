'use strict';
/**
 * Button that handles the opening of the "create new family variant" modal, and the save request of it.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'oro/messenger',
        'pim/form',
        'pim/family-edit-form/add-family-variant-form',
        'pim/template/family/tab/variants/validation-error',
        'bootstrap-modal'
    ],
    function(
        $,
        _,
        __,
        Backbone,
        messenger,
        BaseForm,
        CreateForm,
        errorTemplate
    ) {
        return BaseForm.extend({
            errorTemplate: _.template(errorTemplate),
            className: 'AknActionButton',
            modal: null,

            events: {
                'click': 'openModal'
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(__('pim_enrich.entity.family.variant.add_variant'));
            },

            /**
             * Open the modal containing the form to create a new family variant.
             */
            openModal() {
                this.modal = new Backbone.BootstrapModal({
                    className: 'modal modal--fullPage add-family-variant-modal',
                    content: '',
                    cancelText: __('pim_enrich.entity.family.variant.cancel'),
                    okText: __('pim_enrich.entity.family.variant.create'),
                    okCloses: false
                });

                this.modal.open();
                this.modal.on('ok', () => {
                    this.saveFamilyVariant();
                });

                const modalBody = this.modal.$('.modal-body');

                this.form = new CreateForm();
                this.form.configure();
                this.form.setFamily(this.getFormData());
                this.form.setElement(modalBody).render();

                this.form.$('.AknMessageBox').hide();
            },

            /**
             * Action made when user validates the modal.
             */
            saveFamilyVariant() {
                this.form.$('.validation-errors').remove();
                this.form.$('.AknMessageBox').hide();
                this.form.saveModel();

                $.ajax({
                    method: 'POST',
                    url: Routing.generate(
                        'pim_enrich_family_variant_rest_create'
                    ),
                    data: JSON.stringify(this.form.getFormData())
                })
                .done(() => {
                    this.modal.close();
                    messenger.notify(
                        'success',
                        _.__('pim_enrich.form.family.tab.variant.flash.family_variant_created')
                    );
                })
                .fail((xhr) => {
                    const response = xhr.responseJSON;

                    _.each(response.values, (error) => {
                        if ('code' === error.path) {
                            this.form.$('.error-code').append(
                                this.errorTemplate({
                                    errors: [error.message]
                                })
                            );
                        } else {
                            this.form.$('.AknMessageBox').show();
                            this.form.$('.error-axis').append(
                                this.errorTemplate({
                                    errors: [error.message]
                                })
                            );
                        }
                    });
                });
            }
        });
    }
);
