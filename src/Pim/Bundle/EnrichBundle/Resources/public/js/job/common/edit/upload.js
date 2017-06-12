'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/template/export/common/edit/upload'
], function ($, _, __, BaseForm, template) {
    return BaseForm.extend({
        template: _.template(template),
        events: {
            'change input[type="file"]': 'addFile',
            'click .clear-field': 'removeFile'
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            this.$el.html(this.template({
                file: this.getFormData().file
            }));

            return this;
        },

        /**
         * When a file is added to the dom input
         */
        addFile: function () {
            var input = this.$('input[type="file"]').get(0);
            if (!input || 0 === input.files.length) {
                return;
            }

            this.setData({file: input.files[0]});

            this.getRoot().trigger('pim_enrich:form:job:file_updated');

            this.render();
        },

        /**
         * When the user remove the file from the input
         */
        removeFile: function () {
            this.setData({file: null});

            this.getRoot().trigger('pim_enrich:form:job:file_updated');

            this.render();
        }
    });
});
