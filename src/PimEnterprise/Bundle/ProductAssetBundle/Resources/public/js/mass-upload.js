'use strict';
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'routing',
        'oro/navigation',
        'pim/dropzonejs',
        'oro/messenger',
        'text!pimee/template/asset/mass-upload',
        'text!pimee/template/asset/mass-upload-row'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        Routing,
        Navigation,
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

            events: {
                'click .navbar-buttons .start': 'startAll',
                'click .navbar-buttons .cancel': 'cancelAll',
                'click .navbar-buttons .import': 'importAll'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.pageTemplate({__: __}));
                this.initializeDropzone();
                return this;
            },

            /**
             * Initialize the dropzone element
             */
            initializeDropzone: function () {
                var myDropzone = new Dropzone(document.body, {
                    url: Routing.generate('pimee_product_asset_rest_upload'),
                    thumbnailWidth: 70,
                    thumbnailHeight: 70,
                    parallelUploads: 4,
                    previewTemplate: this.rowTemplate(),
                    autoQueue: false,
                    previewsContainer: 'tbody',
                    clickable: '.fileinput-button',
                    maxFilesize: 1000
                });

                myDropzone.on('removedfile', function () {
                    if (0 === myDropzone.getFilesWithStatus(Dropzone.SUCCESS).length) {
                        this.$('.navbar-buttons .btn.import').addClass('disabled');
                    }
                    if (0 === myDropzone.getFilesWithStatus(Dropzone.ADDED).length) {
                        this.$('.navbar-buttons .btn.start').addClass('disabled');
                    }
                }.bind(this));

                myDropzone.on('addedfile', function (file) {
                    if (Dropzone.SUCCESS === file.status) {
                        this.setStatus(file);
                        file.previewElement.querySelector('.dz-type').textContent = file.type;

                        return;
                    }

                    $.get(
                        Routing.generate('pimee_product_asset_rest_verify_upload', {
                            filename: encodeURIComponent(file.name)
                        })
                    ).success(function () {
                            $('.navbar-buttons .btn.start').removeClass('disabled');
                        }).fail(function (response) {
                            file.status = Dropzone.ERROR;
                            var message = 'pimee_product_asset.mass_upload.error.filename';
                            if (response.responseJSON) {
                                message = response.responseJSON.error;
                            }
                            file.previewElement.querySelector('.filename .error.text-danger')
                                .textContent = _.__(message);
                        }).complete(function () {
                            this.setStatus(file);
                            file.previewElement.querySelector('.dz-type').textContent = file.type;
                    }.bind(this));

                    if ((0 !== file.type.indexOf('image')) || (file.size > myDropzone.options.maxThumbnailFilesize)) {
                        // This is not an image, or image is too big to generate a thumbnail
                        myDropzone.emit(
                            'thumbnail',
                            file,
                            Routing.generate('pim_enrich_default_thumbnail', {mimeType: file.type})
                        );
                    }
                }.bind(this));

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
                    this.$('.navbar-buttons .btn.cancel').removeClass('hide');
                }.bind(this));

                myDropzone.on('queuecomplete', function () {
                    if (myDropzone.getFilesWithStatus(Dropzone.SUCCESS).length > 0) {
                        this.$('.navbar-buttons .btn.import').removeClass('disabled');
                    }
                    this.$('.navbar-buttons .btn.cancel').addClass('hide');
                    this.$('.navbar-buttons .btn.start').addClass('disabled');
                }.bind(this));

                /**
                 * Delete/Cancel file handler
                 *
                 * @param {Object} file File object to remve
                 */
                myDropzone.removeFile = function (file) {
                    if (Dropzone.SUCCESS === file.status) {
                        $.ajax({
                            url: Routing.generate(
                                'pimee_product_asset_mass_upload_rest_delete',
                                {
                                    filename: encodeURIComponent(file.name)
                                }
                            ),
                            type: 'DELETE'
                        })
                        .success(function () {
                            Dropzone.prototype.removeFile.call(this, file);
                        }.bind(this))
                        .fail(function () {
                            messenger.notificationFlashMessage(
                                'error',
                                _.__('pimee_product_asset.mass_upload.error.delete')
                            );
                        });
                    } else {
                        Dropzone.prototype.removeFile.call(this, file);
                    }
                };

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

            /**
             * Starts uploads
             */
            startAll: function () {
                if (!this.$('.navbar-buttons .btn.start').hasClass('disabled')) {
                    this.myDropzone.enqueueFiles(this.myDropzone.getFilesWithStatus(Dropzone.ADDED));
                }
            },

            /**
             * Cancel all uploads and delete already uploaded files
             */
            cancelAll: function () {
                this.$('.navbar-buttons .btn.import').addClass('disabled');
                this.myDropzone.removeAllFiles(true);
                messenger.notificationFlashMessage(
                    'success',
                    _.__('pimee_product_asset.mass_upload.success.canceled')
                );
            },

            /**
             * Import uploaded files for asset processing
             */
            importAll: function () {
                if (!this.$('.navbar-buttons .btn.import').hasClass('disabled')) {
                    this.$('.navbar-buttons .btn.import').addClass('disabled');
                    $.get(
                        Routing.generate('pimee_product_asset_mass_upload_rest_import')
                    ).done(function (response) {
                        var jobReportUrl = Routing.generate('pim_enrich_job_tracker_show', {id: response.jobId});
                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pimee_product_asset.mass_upload.success.imported')
                        );
                        Navigation.getInstance().setLocation(jobReportUrl);
                    }.bind(this)).fail(function () {
                        messenger.notificationFlashMessage(
                            'error',
                            _.__('pimee_product_asset.mass_upload.error.import')
                        );
                    });
                }
            },

            /**
             * Change asset status in the grid
             *
             * @param {Object} file
             */
            setStatus: function (file) {
                var statusElement = file.previewElement.querySelector('.dz-status');
                statusElement.classList.add(file.status.toLowerCase());
                var statusKey = 'pimee_product_asset.mass_upload.status.' + file.status;
                statusElement.textContent = _.__(statusKey);
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
