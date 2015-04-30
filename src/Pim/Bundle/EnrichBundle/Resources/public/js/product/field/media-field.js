'use strict';

define([
        'jquery',
        'pim/field',
        'underscore',
        'routing',
        'pim/attribute-manager',
        'text!pim/template/product/field/media'
    ],
    function ($, Field, _, Routing, AttributeManager, fieldTemplate) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'media',
            events: {
                'change .edit input[type="file"]': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            getTemplateContext: function () {
                var deferred = $.Deferred();

                Field.prototype.getTemplateContext.apply(this, arguments)
                    .done(_.bind(function (templateContext) {
                        this.setMediaUrl(templateContext);
                        deferred.resolve(templateContext);
                    }, this));

                return deferred.promise();
            },
            setMediaUrl: function (templateContext) {
                if (templateContext.value.value && templateContext.value.value.filePath) {
                    var filename = templateContext.value.value.filePath;
                    filename = filename.substring(filename.lastIndexOf('/') + 1);
                    templateContext.mediaUrl = Routing.generate('pim_enrich_media_show', {
                        filename: filename
                    });
                } else {
                    templateContext.mediaUrl = null;
                }
            },
            renderCopyInput: function (context, locale, scope) {
                context.value = AttributeManager.getValue(
                    this.model.get('values'),
                    this.attribute,
                    locale,
                    scope
                );

                this.setMediaUrl(context);

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
            handleProcess: function (e) {
                this.$('.progress .bar').css({
                    width: ((e.loaded / e.total) * 100) + '%'
                });
            }
        });
    }
);
