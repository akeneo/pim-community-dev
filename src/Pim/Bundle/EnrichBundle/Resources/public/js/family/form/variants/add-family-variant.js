'use strict';
/**
 * Button that handles the opening of the "create new family variant" modal.
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
        'oro/messenger',
        'pim/form',
        'pim/form-modal'
    ],
    function(
        $,
        _,
        __,
        messenger,
        BaseForm,
        FormModal
    ) {
        return BaseForm.extend({
            className: 'AknButton AknButton--action AknButton--small add-variant',
            modal: null,

            events: {
                'click': 'openModal'
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(__('pim_enrich.entity.family_variant.module.create.label'));
                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Open the modal containing the form to create a new family variant.
             */
            openModal() {
                const modalParameters = {
                    className: 'modal modal--fullPage add-family-variant-modal',
                    content: '',
                    cancelText: __('pim_common.cancel'),
                    okText: __('pim_common.create'),
                    okCloses: false
                };

                const formModal = new FormModal(
                    'pim-family-variant-create-form',
                    this.submitForm.bind(this),
                    modalParameters,
                    {family: this.getFormData().code}
                );

                formModal.open();
            },

            /**
             * Action made when user submit the modal.
             */
            submitForm(formModal) {
                return formModal.saveFamilyVariant()
                    .then((familyVariant) => {
                        messenger.notify(
                            'success',
                            _.__('pim_enrich.entity.family_variant.flash.create.success')
                        );
                        this.getRoot().trigger('pim_enrich.entity.family.family_variant.post_create', familyVariant);
                    });
            }
        });
    }
);
