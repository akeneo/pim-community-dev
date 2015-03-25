"use strict";

define([
        'pim/field',
        'underscore',
        'routing',
        'text!pim/template/product/field/media'
    ],
    function (Field, _, Routing, fieldTemplate) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            fieldType: 'media',
            events: {
                'change input': 'updateModel'
            },
            renderInput: function(context) {
                return this.fieldTemplate(context);
            },
            getTemplateContext: function() {
                var promise = $.Deferred();

                Field.prototype.getTemplateContext.apply(this, arguments)
                    .done(_.bind(function(templateContext) {
                        console.log(this.getCurrentValue().value);
                        if (this.getCurrentValue().value && this.getCurrentValue().value.filePath) {
                            var filename = this.getCurrentValue().value.filePath;
                            filename = filename.substring(filename.lastIndexOf('/') + 1);
                            templateContext.mediaUrl = Routing.generate('pim_enrich_media_show', {
                                filename: filename
                            });
                        } else {
                            templateContext.mediaUrl = null;
                        }

                        promise.resolve(templateContext);
                    }, this));

                return promise.promise();
            },
            updateModel: function (event) {
                var input = event.currentTarget;

                var formData = new FormData();
                formData.append('file', input.files[0]);

                this.$('.progress .bar').css({opacity: 1});

                $.ajax({
                    url: Routing.generate('pim_enrich_media_rest_post'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData:false,
                    xhr: _.bind(function() {
                        var myXhr = $.ajaxSettings.xhr();
                        if(myXhr.upload){
                            myXhr.upload.addEventListener('progress', _.bind(this.handleProcess, this), false);
                        }

                        return myXhr;
                    }, this)
                }).done(_.bind(function(data) {
                    this.setCurrentValue(data);
                    this.render();
                    this.$('.progress .bar').css({opacity: 0});
                }, this));
            },
            handleProcess: function(e) {
                this.$('.progress .bar').css({opacity: 1}).animate({
                    width: ((e.loaded/e.total) * 100) + '%'
                });
            }
        });
    }
);
