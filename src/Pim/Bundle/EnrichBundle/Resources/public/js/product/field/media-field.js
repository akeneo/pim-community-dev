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
        'text!pim/template/product/field/media',
        'pim/dialog',
        'oro/mediator',
        'jquery.slimbox'
    ],
    function ($, Field, _, Routing, AttributeManager, fieldTemplate, Dialog, mediator) {
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
                    .then(_.bind(function (templateContext) {
                        templateContext.mediaUrl = this.getMediaUrl(templateContext.value.value);
                        templateContext.inUpload = !this.isReady();
                        return templateContext;
                    }, this));
            },
            getMediaUrl: function (value) {
                if (value && value.filePath) {
                    var filename = value.filePath;
                    filename = filename.substring(filename.lastIndexOf('/') + 1);
                    return Routing.generate('pim_enrich_media_show', {
                        filename: filename
                    });
                }

                return null;
            },
            renderCopyInput: function (context, locale, scope) {
                context.value = AttributeManager.getValue(
                    this.model.get('values'),
                    this.attribute,
                    locale,
                    scope
                );

                context.mediaUrl = this.getMediaUrl(context.value.value);

                return Field.prototype.renderCopyInput.apply(this, arguments);
            },
            updateModel: function () {
                if (!this.isReady()) {
                    Dialog.alert(_.__(
                        'pim_enrich.entity.product.info.already_in_upload',
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
                    xhr: _.bind(function () {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener('progress', _.bind(this.handleProcess, this), false);
                        }

                        return myXhr;
                    }, this)
                }).done(_.bind(function (data) {
                    this.setUploadContextValue(data);
                    this.render();
                }, this)).then(_.bind(function () {
                    this.$('> .media-field .progress').css({opacity: 0});
                    this.setReady(true);
                    this.uploadContext = {};
                }, this));
            },
            clearField: function () {
                this.setCurrentValue(this.attribute.empty_value);

                this.render();
            },
            handleProcess: function (e) {
                if (this.uploadContext.locale === this.context.locale &&
                    this.uploadContext.scope === this.context.scope
                ) {
                    this.$('> .media-field .progress').css({opacity: 1});
                    this.$('> .media-field .progress .bar').css({
                        width: ((e.loaded / e.total) * 100) + '%'
                    });
                }
            },
            previewImage: function () {
                var mediaUrl = this.getMediaUrl(this.getCurrentValue().value);
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

                productValue.value = value;
                mediator.trigger('entity:form:edit:update_state');
            }
        });
    }
);
