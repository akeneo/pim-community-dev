'use strict';

define(['pim/controller/base'], function (BaseController) {
    return BaseController.extend({
        formPromise: null,

        /**
         * {@inheritdoc}
         */
        renderRoute: function (route, path) {
            this.formPromise = this.renderForm(route, path);

            return this.formPromise;
        },

        /**
         * {@inheritdoc}
         */
        remove: function () {
            if (null === this.formPromise) {
                return;
            }

            this.formPromise.then((form) => {
                form.shutdown();
            });

            BaseController.prototype.remove.apply(this, arguments);
        }
    });
});
