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
        'pim/form-builder',
        'pim/security-context',
        'pim/template/common/modal-centered'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        modalContentTemplate,
        FetcherRegistry,
        BootstrapModal,
        FormBuilder,
        SecurityContext,
        modalTemplate
    ) {
        return BaseForm.extend({
            template: _.template(template),
            modalTemplate: _.template(modalTemplate),
            modalContentTemplate: _.template(modalContentTemplate),

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
                if (this.modal) {
                    this.modal.close();
                    this.modal.$el.off();
                }
            },

            /**
             * Returns a list of choices that are allowed by permissions
             * @return {Object} choices
             */
            getAllowedChoices(choices) {
                return Object.values(choices).filter(choice => {
                    return SecurityContext.isGranted(choice.aclResourceId);
                });
            },

            /**
             * Opens the selection modal with the configured choices
             * If there's only one available choice, directly open the form
             * for that choice.
             *
             * @return {Backbone.BootstrapModal} The modal
             */
            openModal() {
                if (this.modal) {
                    this.closeModal();
                }

                const { choices, modalTitle, subTitle } = this.config;
                const allowedChoices = this.getAllowedChoices(choices);

                if (1 === allowedChoices.length) {
                    const firstChoice = allowedChoices[0];

                    return this.openFormModal(null, firstChoice.form);
                }

                const translatedChoices = [];
                Object.keys(allowedChoices).forEach((key) => {
                    translatedChoices[key] = allowedChoices[key];
                    translatedChoices[key].title = __(translatedChoices[key].title);
                });

                this.modal = new Backbone.BootstrapModal({
                    title: __(modalTitle),
                    subtitle: __(subTitle),
                    okText: '',
                    template: this.modalTemplate,
                    content: this.modalContentTemplate({
                        choices: translatedChoices,
                    })
                }).open();

                this.modal.$el.on('click', '.cancel', this.closeModal.bind(this));
                this.modal.$el.on('click', '.product-choice', this.openFormModal.bind(this));

                return this.modal;
            },

            /**
             * {@inheritdoc}
             */
            shutdown: function () {
                if (this.modal) {
                    this.modal.$el.off();
                }

                BaseForm.prototype.shutdown.apply(this, arguments);
            },

            /**
             * Opens a form model for the selected choice. If formName is passed in, it
             * overrides the formName from the event target element.
             *
             * @param  {jQuery.Event} event The click event from the selection modal
             * @param  {String} formName The name of the form extension defined for a choice
             * @return {Promise}
             */
            openFormModal(event, formName) {
                const form = formName || $(event.currentTarget).attr('data-form');

                return FormBuilder.build(form).then(modal => {
                    this.closeModal();
                    modal.open();
                });
            },

            /**
             * Render the create button
             * If the user is not allowed to access the forms for the choices
             * don't render the create button.
             */
            render() {
                const { choices, buttonTitle } = this.config;

                if (0 === this.getAllowedChoices(choices).length) {
                    this.$el.hide();

                    return;
                }

                this.$el.html(this.template({
                    buttonTitle: __(buttonTitle)
                }));
            }
        });
    });
