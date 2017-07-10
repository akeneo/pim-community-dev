

import _ from 'underscore';
import BaseController from 'pim/controller/base';
import FormBuilder from 'pim/form-builder';
export default BaseController.extend({
    initialize: function (options) {
        this.options = options;
    },

            /**
             * {@inheritdoc}
             */
    renderRoute: function () {
        return FormBuilder.build('pim-' + this.options.config.entity + '-index')
                    .then(function (form) {
                        form.setElement(this.$el).render();
                    }.bind(this));
    }
});

