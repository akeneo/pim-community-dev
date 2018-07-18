/**
 * Media field (using back-end FileInfo)
 *
 * @author    Marie Gautier <marie.gautier@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'pim/form/common/fields/field',
    'underscore',
    'oro/translator',
    'routing',
    'pim/template/form/common/fields/media',
    'oro/mediator',
    'oro/messenger',
    'pim/media-url-generator',
    'jquery.slimbox'
],
function (
    $,
    BaseField,
    _,
    __,
    Routing,
    template,
    mediator,
    messenger,
    MediaUrlGenerator
) {
    return BaseField.extend({
        template: _.template(template),
        events: {
            'change input[type="file"]': 'uploadMedia',
            'click  .clear-field': 'clearField',
            'click  .open-media': 'previewImage'
        },

        getTemplateContext: function () {
            return BaseField.prototype.getTemplateContext.apply(this, arguments)
                .then(function (templateContext) {
                    templateContext.mediaUrlGenerator = MediaUrlGenerator;

                    return templateContext;
                }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend(templateContext, {
                media: this.getFormData()[this.fieldName],
                readOnly: this.readOnly,
                uploadLabel: __('pim_common.media_upload')
            }));
        },

        uploadMedia: function () {
            const input = this.$('input[type="file"]')[0];
            if (!input || 0 === input.files.length) {
                return;
            }

            const formData = new FormData();
            formData.append('file', input.files[0]);

            $.ajax({
                url: Routing.generate('pim_enrich_media_rest_post'),
                type: 'POST',
                data: formData,
                contentType: false,
                cache: false,
                processData: false,
                xhr: function () {
                    const myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) {
                        myXhr.upload.addEventListener('progress', this.handleProcess.bind(this), false);
                    }

                    return myXhr;
                }.bind(this)
            })
            .done(function (data) {
                this.setUploadContextValue(data);
                this.render();
            }.bind(this))
            .fail(function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ?
                    xhr.responseJSON.message :
                    __('pim_enrich.entity.product.error.upload');
                messenger.enqueueMessage('error', message);
            })
            .always(function () {
                this.$('> .akeneo-media-uploader-field .progress').css({opacity: 0});
            }.bind(this));
        },

        clearField: function () {
            this.updateModel({
                filePath: null,
                originalFilename: null
            });

            this.render();
        },

        handleProcess: function (e) {
            this.$('> .akeneo-media-uploader-field .progress').css({opacity: 1});
            this.$('> .akeneo-media-uploader-field .progress .bar').css({
                width: ((e.loaded / e.total) * 100) + '%'
            });
        },

        previewImage: function () {
            const mediaUrl = MediaUrlGenerator.getMediaShowUrl(this.getFormData()[this.fieldName].filePath, 'preview');
            if (mediaUrl) {
                $.slimbox(mediaUrl, '', {overlayOpacity: 0.3});
            }
        },

        setUploadContextValue: function (value) {
            this.updateModel(value);

            mediator.trigger('pim_enrich:form:entity:update_state');
        }
    });
});
