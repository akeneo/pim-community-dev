'use strict';
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'pim/dropzonejs',
        'oro/messenger',
        'text!pimee/template/asset/mass-upload',
        'text!pimee/template/asset/mass-upload-row'
    ],
    function (
        $,
        _,
        Backbone,
        Routing,
        Dropzone,
        messenger,
        pageTemplate,
        rowTemplate
    ) {
        /**
         * Override to be able to use template root different other than 'div'
         *
         * @param string
         *
         * @returns {*}
         */
        Dropzone.createElement = function (string) {
            var el = $(string);

            return el[0];
        };
        Dropzone.autoDiscover = false;

        return Backbone.View.extend({
            myDropzone: null,
            pageTemplate: _.template(pageTemplate),
            rowTemplate: _.template(rowTemplate),

            initializeDropzone: function () {
                var myDropzone = new Dropzone(document.body, {
                    url: Routing.generate('pimee_product_asset_upload'),
                    thumbnailWidth: 70,
                    thumbnailHeight: 70,
                    parallelUploads: 4,
                    previewTemplate: this.rowTemplate(),
                    autoQueue: false,
                    previewsContainer: 'tbody',
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
                        .textContent = _.__(error.error);
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
                    type: 'GET'
                }).done(function (response) {
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
                        myDropzone.emit('complete', mockFile);
                        myDropzone.emit('success', mockFile, {});
                    });
                }).fail(function () {
                    messenger.notificationFlashMessage(
                        'error',
                        _.__('pimee_product_asset.mass_upload.error.list')
                    );
                });

                this.myDropzone = myDropzone;
            },

            events: {
                'click .navbar-buttons .start': 'startAll',
                'click .navbar-buttons .cancel': 'cancelAll',
                'click .navbar-buttons .schedule': 'scheduleAll'
            },

            render: function () {
                this.$el.html(this.pageTemplate());
                this.initializeDropzone();
                return this;
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
                $.get(
                    Routing.generate('pimee_product_asset_mass_upload_rest_schedule')
                ).done(function (response) {
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
                }.bind(this)).fail(function () {
                    messenger.notificationFlashMessage(
                        'error',
                        _.__('pimee_product_asset.mass_upload.error.schedule')
                    );
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
             *
             * @returns {Object|null}
             */
            findFile: function (filename) {
                return _.findWhere(this.myDropzone.files, {name: filename});
            }
        });
    }
);
