'use strict';
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'pim/dropzonejs',
        'oro/messenger'
    ],
    function ($, _,
              Backbone,
              Routing,
              Dropzone,
              messenger) {
        /**
         * Override to be able to use template root different other than 'div'
         * @param string
         * @returns {*}
         */
        Dropzone.createElement = function (string) {
            var el = $(string);
            return el[0];
        };
        Dropzone.autoDiscover = false;

        return Backbone.View.extend({
            el: 'body',
            myDropzone: null,

            initialize: function () {
                // Get the template HTML and remove it from the doument
                var previewNode = this.$el.find('#template');
                previewNode.id = '';

                var previewTemplate = previewNode.parent().html();
                previewNode.parent().html('');

                var myDropzone = new Dropzone(this.el, {
                    url: Routing.generate('pimee_product_asset_upload'),
                    thumbnailWidth: 70,
                    thumbnailHeight: 70,
                    parallelUploads: 4,
                    previewTemplate: previewTemplate,
                    autoQueue: false,
                    previewsContainer: '#previews',
                    clickable: '.fileinput-button',
                    maxFilesize: 1000
                });

                myDropzone.on('addedfile', function (file) {
                    if (Dropzone.SUCCESS === file.status) {
                        this.setStatus(file);
                        file.previewElement.querySelector('.dz-type').textContent = file.type;
                        return;
                    }
                    $.ajax({
                        url: Routing.generate('pimee_product_asset_rest_verify_upload', {filename: file.name}),
                        type: 'GET',
                        error: function (response) {
                            file.status = Dropzone.ERROR;
                            file.previewElement.querySelector('.filename .error.text-danger')
                                .textContent = _.__(response.responseJSON.error);
                        },
                        complete: function () {
                            this.setStatus(file);
                            file.previewElement.querySelector('.dz-type').textContent = file.type;
                        }.bind(this)
                    });
                }.bind(this));

                myDropzone.on('removedfile', function (file) {
                    if (0 === myDropzone.getFilesWithStatus(Dropzone.SUCCESS).length) {
                        document.querySelector('.navbar-buttons .btn.schedule').style.display = 'none';
                    }
                    if (Dropzone.SUCCESS === file.status) {
                        return $.ajax({
                            url: Routing.generate('pimee_product_asset_delete', {filename: file.name}),
                            type: 'DELETE'
                        });
                    }
                });

                myDropzone.on('success', function (file) {
                    this.setStatus(file);
                    file.previewElement.querySelector('div.progress').className = 'progress success';
                    file.previewElement.querySelector('div.progress .bar').style.width = '100%';
                }.bind(this));

                myDropzone.on('error', function (file, error) {
                    file.previewElement.querySelector('.filename .error.text-danger')
                        .textContent =  _.__(error.error);
                    this.setStatus(file);
                }.bind(this));

                myDropzone.on('sending', function (file) {
                    this.setStatus(file);
                }.bind(this));

                // Hide the total progress bar when nothing's uploading anymore
                myDropzone.on('queuecomplete', function () {
                    if (myDropzone.getFilesWithStatus(Dropzone.SUCCESS).length > 0) {
                        this.$('.navbar-buttons .btn.schedule').show();
                    }
                }.bind(this));

                $.ajax({
                    url: Routing.generate('pimee_product_asset_mass_upload_rest_list'),
                    type: 'GET',
                    success: function (response) {
                        _.each(response.files, function (file) {
                            var mockFile = {
                                name: file.name,
                                type: file.type,
                                size: file.size,
                                status: Dropzone.SUCCESS,
                                upload: {progress: 100}
                            };
                            myDropzone.files.push(mockFile);
                            myDropzone.emit('addedfile', mockFile);
                            //myDropzone.emit('thumbnail', mockFile, '/image/url');
                            myDropzone.emit('complete', mockFile);
                            myDropzone.emit('success', mockFile, {});
                        });
                    }
                });

                $('.navbar-buttons .start').on('click', function () {
                    this.startAll();
                }.bind(this));
                $('.navbar-buttons .cancel').on('click', function () {
                    this.cancelAll();
                }.bind(this));
                $('.navbar-buttons .schedule').on('click', function () {
                    this.scheduleAll();
                }.bind(this));

                this.myDropzone = myDropzone;
            },

            /**
             * Starts uploads
             */
            startAll: function () {
                this.myDropzone.enqueueFiles(this.myDropzone.getFilesWithStatus(Dropzone.ADDED));
            },

            /**
             * Cancel all uploads and delete already uploaded files
             */
            cancelAll: function () {
                this.$('.navbar-buttons .btn.schedule').hide();
                this.myDropzone.removeAllFiles(true);
                messenger.notificationFlashMessage(
                    'success',
                    _.__('pimee_product_asset.mass_upload.canceled')
                );
            },

            /**
             * Schedule uploaded files for asset processing
             */
            scheduleAll: function () {
                this.$('.navbar-buttons .btn.schedule').hide();
                $.ajax({
                    url: Routing.generate('pimee_product_asset_mass_upload_rest_schedule'),
                    type: 'GET',
                    success: function (response) {
                        _.each(response.result, function (result) {
                            var file = this.findFile(result.file);
                            if (result.error) {
                                file.status = Dropzone.ERROR;
                                this.setStatus(file);
                                file.previewElement.querySelector('.filename .error.text-danger')
                                    .textContent = _.__(result.error);
                            } else {
                                this.myDropzone.removeFile(file);
                            }
                        }.bind(this));
                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pimee_product_asset.mass_upload.scheduled')
                        );
                    }.bind(this)
                });
            },

            /**
             * Change asset status in the grid
             *
             * @param {Object} file
             */
            setStatus: function (file) {
                var statusElement = file.previewElement.querySelector('.dz-status');
                statusElement.classList.add(file.status.toLowerCase());
                statusElement.textContent = _.__(file.status).toUpperCase();
            },

            /**
             * Find a dropzone file with filename
             *
             * @param {String} filename
             * @returns {Boolean}
             */
            findFile: function (filename) {
                var found = null;
                var acceptedFiles = this.myDropzone.files;
                _.each(acceptedFiles, function (file) {
                    if (file.name === filename) {
                        found = file;
                    }
                });
                return found;
            }
        });
    }
);
