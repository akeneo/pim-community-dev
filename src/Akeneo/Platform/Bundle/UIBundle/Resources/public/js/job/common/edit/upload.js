'use strict';

/**
 * Displays a drop zone to upload a file.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/user-context',
    'oro/messenger',
    'pim/template/export/common/edit/upload'
], function ($, _, __, BaseForm, UserContext, messenger, template) {
    return BaseForm.extend({
        template: _.template(template),
        events: {
            'change input[type="file"]': 'addFile',
            'click .clear-field': 'removeFile'
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            this.config = config.config;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            this.$el.html(this.template({
                file: this.getFormData().file,
                type: this.config.type,
                __
            }));

            this.delegateEvents();

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

            const uploadedFile = input.files[0];

            if (!this.isUploadedFilesizeValid(uploadedFile)) {
                return;
            }

            this.setData({file: uploadedFile});

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
        },

        /**
         * Validate file size in MB
         *
         * @param uploadedFile
         * @returns {boolean}
         */
        isUploadedFilesizeValid(uploadedFile) {
            const fileSize = uploadedFile.size;
            const environment = UserContext.get('meta');

            if (fileSize > environment.environment.upload_max_filesize) {
                messenger.notify('error', __('pim_import_export.entity.import_profile.flash.upload.error_too_big'));

                return false;
            }

            return true;
        }
    });
});
