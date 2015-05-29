'use strict';

define([
        'jquery',
        'pim/field',
        'underscore',
        'routing',
        'pim/attribute-manager',
        'text!pim/template/product/field/media',
        'jquery.slimbox'
    ],
    function ($, Field, _, Routing, AttributeManager, fieldTemplate) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'media',
            events: {
                'change .edit input[type="file"]': 'updateModel',
                'click  .clear-field': 'clearField',
                'click  .open-media': 'previewImage'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments)
                    .then(_.bind(function (templateContext) {
                        templateContext.mediaUrl = this.getMediaUrl(templateContext.value.value);

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
            updateModel: function (event) {
                var input = event.currentTarget;

                var formData = new FormData();
                formData.append('file', input.files[0]);

                this.$('.progress').css({opacity: 1});
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
                    this.setCurrentValue(data);
                    this.render();
                    this.$('.progress').css({opacity: 0});
                }, this));
            },
            clearField: function () {
                this.setCurrentValue(AttributeManager.getEmptyValue(this.attribute));

                this.render();
            },
            handleProcess: function (e) {
                this.$('.progress .bar').css({
                    width: ((e.loaded / e.total) * 100) + '%'
                });
            },
            previewImage: function () {
                var mediaUrl = this.getMediaUrl(this.getCurrentValue().value);
                if (mediaUrl) {
                    $.slimbox(mediaUrl, '', {overlayOpacity: 0.3});
                }
            }
        });
    }
);
