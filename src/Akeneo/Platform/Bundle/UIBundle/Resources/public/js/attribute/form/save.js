'use strict';

define(
    [
        'pim/form/common/save-form',
        'oro/messenger'
    ],
    function (BaseSave, messenger) {
        return BaseSave.extend({
            fail: function (response) {
                let errorMessage = this.updateFailureMessage;

                switch (response.status) {
                case 400:
                    this.getRoot().trigger(
                        'pim_enrich:form:entity:bad_request',
                        {'sentData': this.getFormData(), 'response': response.responseJSON}
                    );
                    errorMessage = response.responseJSON[0] !== undefined ?
                        response.responseJSON[0].message :
                        errorMessage;
                    break;
                case 500:
                    const message = response.responseJSON ? response.responseJSON : response;
                    this.getRoot().trigger('pim_enrich:form:entity:error:save', message);
                    break;
                default:
                }

                messenger.notify(
                    'error',
                    errorMessage
                );
            }
        });
    }
);
