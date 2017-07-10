

import FormController from 'pim/controller/form';
import securityContext from 'pim/security-context';
import configProvider from 'pim/form-config-provider';
export default FormController.extend({
            /**
             * {@inheritdoc}
             */
    afterSubmit: function () {
        securityContext.fetch();
        configProvider.clear();

        FormController.prototype.afterSubmit.apply(this, arguments);
    }
});

