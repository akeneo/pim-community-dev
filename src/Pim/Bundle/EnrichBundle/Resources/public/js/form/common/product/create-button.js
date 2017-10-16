/**
 * Create product and product-model extension
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/template/product/create-button',
        'pim/template/product/create-modal-content',
        'pim/fetcher-registry',
        'bootstrap-modal',
        'pim/form-builder'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        templateModal,
        FetcherRegistry,
        BootstrapModal,
        FormBuilder
    ) {
        return BaseForm.extend({
            template: _.template(template),
            templateModal: _.template(templateModal),

            events: {
                'click .create-product-button': 'openModal'
            },

            /**
             * {@inheritdoc}
             */
            initialize(config) {
                this.config = config.config;
                this.modal = null;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Closes the selection modal and unbinds the click events
             */
            closeModal() {
                this.modal.close();
                this.modal.$el.off();
            },

            /**
             * Opens the selection modal with the configured choices
             * @return {Backbone.BootstrapModal} The modal
             */
            openModal() {
                if (this.modal) this.closeModal();

                this.modal = new Backbone.BootstrapModal({
                    content: this.templateModal({
                        choices: this.config.choices,
                        modalTitle: __(this.config.modalTitle),
                        subTitle: __(this.config.subTitle)
                    })
                }).open();

                this.modal.$el.find('.modal-footer').remove();
                this.modal.$el.addClass('modal--fullPage modal--columns');
                this.modal.$el.on('click', '.AknFullPage-cancel', this.closeModal.bind(this));
                this.modal.$el.on('click', '.product-choice', this.openFormModal.bind(this));

                return this.modal;
            },

            /**
             * Opens the form modal for the selected choice e.g. create product form
             * @param  {jQuery.Event} event The event with the selected choice as target
             */
            openFormModal(event) {
                const form = $(event.currentTarget).attr('data-form');

                FormBuilder.build(form).then(modal => {
                    this.closeModal();
                    modal.open();
                });
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html(this.template({
                    buttonTitle: __(this.config.buttonTitle)
                }));
            }
        });
    });
