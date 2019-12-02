'use strict';
/**
 * Media field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'pim/field',
        'underscore',
        'routing',
        'pim/attribute-manager',
        'pim/template/product/field/media',
        'pim/dialog',
        'oro/mediator',
        'oro/messenger',
        'pim/media-url-generator',
        'jquery.slimbox'
    ],
    function ($, Field, _, Routing, AttributeManager, fieldTemplate, Dialog, mediator, messenger, MediaUrlGenerator) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .edit .field-input:first input[type="file"]': 'updateModel',
                'click  .clear-field': 'clearField',
                'click  .open-media': 'previewImage'
            },
            uploadContext: {},
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments)
                    .then(function (templateContext) {
                        templateContext.inUpload          = !this.isReady();
                        templateContext.mediaUrlGenerator = MediaUrlGenerator;

                        return templateContext;
                    }.bind(this));
            },

            renderCopyInput: function (value) {
                return this.getTemplateContext()
                    .then(function (context) {
                        var copyContext = $.extend(true, {}, context);
                        copyContext.value = value;
                        copyContext.context.locale    = value.locale;
                        copyContext.context.scope     = value.scope;
                        copyContext.editMode          = 'view';
                        copyContext.mediaUrlGenerator = MediaUrlGenerator;

                        return this.renderInput(copyContext);
                    }.bind(this));
            },
            updateModel: function () {
                if (!this.isReady()) {
                    Dialog.alert(_.__(
                        'pim_enrich.entity.product.flash.update.already_in_upload',
                        {'locale': this.context.locale, 'scope': this.context.scope}
                    ));
                }

                var input = this.$('.edit .field-input:first input[type="file"]').get(0);
                if (!input || 0 === input.files.length) {
                    return;
                }

                var formData = new FormData();
                formData.append('file', input.files[0]);

                this.setReady(false);
                this.uploadContext = {
                    'locale': this.context.locale,
                    'scope':  this.context.scope
                };

                $.ajax({
                    url: Routing.generate('pim_enrich_media_rest_post'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    xhr: function () {
                        var myXhr = $.ajaxSettings.xhr();
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
                    var message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message :
                        _.__('pim_enrich.entity.product.flash.update.file_upload');
                    messenger.notify('error', message);
                })
                .always(function () {
                    this.$('> .akeneo-media-uploader-field .progress').css({opacity: 0});
                    this.setReady(true);
                    this.uploadContext = {};
                }.bind(this));
            },
            clearField: function () {
                this.setCurrentValue({
                    filePath: null,
                    originalFilename: null
                });

                this.render();
            },
            handleProcess: function (e) {
                if (this.uploadContext.locale === this.context.locale &&
                    this.uploadContext.scope === this.context.scope
                ) {
                    this.$('> .akeneo-media-uploader-field .progress').css({opacity: 1});
                    this.$('> .akeneo-media-uploader-field .progress .bar').css({
                        width: ((e.loaded / e.total) * 100) + '%'
                    });
                }
            },
            previewImage: function () {
                var mediaUrl = MediaUrlGenerator.getMediaShowUrl(this.getCurrentValue().data.filePath, 'preview');
                if (mediaUrl) {
                    $.slimbox(mediaUrl, '', {overlayOpacity: 0.3});
                }
            },
            setUploadContextValue: function (value) {
                var productValue = AttributeManager.getValue(
                    this.model.get('values'),
                    this.attribute,
                    this.uploadContext.locale,
                    this.uploadContext.scope
                );

                productValue.data = value;
                mediator.trigger('pim_enrich:form:entity:update_state');
            }
        });
    }
);
