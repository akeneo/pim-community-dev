'use strict';

define(
    [
        'pim/form/common/creation/modal',
    ],
    function (
        BaseModal,
    ) {
        return BaseModal.extend({
            events: {
                'keyup input': 'checkReadyToSubmit'
            },

            checkReadyToSubmit() {
                this.$el.parent().find('.AknButton.ok').toggleClass('AknButton--disabled', !this.isReadyToSubmit());
            },

            isReadyToSubmit() {
                const data = this.getFormData();

                return !Object.keys(this.extensions).some(extensionKey => {
                    const extension = this.getExtension(extensionKey);

                    return extension.config.required &&
                        (undefined === data[extension.fieldName] || '' === data[extension.fieldName]);
                });
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.checkReadyToSubmit();

                return BaseModal.prototype.render.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            confirmModal() {
                if (!this.isReadyToSubmit()) return;

                return BaseModal.prototype.confirmModal.apply(this, arguments);
            }
        });
    }
);
