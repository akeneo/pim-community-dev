'use strict';
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/router',
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
        router,
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

        var $navbarButtons;
        var $importButton;
        var $startButton;
        var $cancelButton;

        return Backbone.View.extend({
            myDropzone: null,
            pageTemplate: _.template(pageTemplate),
            rowTemplate: _.template(rowTemplate),

            events: {
                'click .AknTitleContainer .start:not(.AknButton--disabled)': 'startAll',
                'click .AknTitleContainer .cancel:not(.AknButton--disabled)': 'cancelAll',
                'click .AknTitleContainer .import:not(.AknButton--disabled)': 'importAll'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.pageTemplate({__: __}));

                $navbarButtons = $('.AknTitleContainer-rightButtons');
                $importButton = $navbarButtons.find('.import');
                $startButton = $navbarButtons.find('.start');
                $cancelButton = $navbarButtons.find('.cancel');

                this.initializeDropzone();

                return this;
            },

            /**
             * Initialize the dropzone element
             */
            initializeDropzone: function () {
                var myDropzone = new Dropzone('body', {
                    url: router.generate('pimee_product_asset_rest_upload'),
                    thumbnailWidth: 70,
                    thumbnailHeight: 70,
                    parallelUploads: 4,
                    previewTemplate: this.rowTemplate(),
                    autoQueue: false,
                    previewsContainer: 'tbody',
                    clickable: '.upload-zone-container',
                    maxFilesize: 1000
                });

                myDropzone.on('removedfile', function () {
                    if (0 === myDropzone.getFilesWithStatus(Dropzone.SUCCESS).length) {
                        $importButton.addClass('AknButton--disabled');
                    }
                    if (0 === myDropzone.getFilesWithStatus(Dropzone.ADDED).length) {
                        $startButton.addClass('AknButton--disabled');
                    }
                    if (0 === myDropzone.files.length) {
                        $cancelButton.addClass('AknButton--hidden');
                    }
                }.bind(this));

                myDropzone.on('addedfile', function (file) {
                    if (Dropzone.SUCCESS === file.status) {
                        this.setStatus(file);
                        file.previewElement.querySelector('.dz-type').textContent = file.type;

                        return;
                    }

                    $.get(
                        router.generate('pimee_product_asset_rest_verify_upload', {
                            filename: encodeURIComponent(file.name)
                        })
                    ).done(function () {
                        $startButton.removeClass('AknButton--disabled');
                        $cancelButton.removeClass('AknButton--hidden');
                    }).fail(function (response) {
                        file.status = Dropzone.ERROR;
                        var message = 'pimee_product_asset.mass_upload.error.filename';
                        if (response.responseJSON) {
                            message = response.responseJSON.error;
                        }
                        file.previewElement.querySelector('.AknFieldContainer-validationError')
                            .textContent = __(message);
                    }).complete(function () {
                        this.setStatus(file);
                        file.previewElement.querySelector('.dz-type').textContent = file.type;
                    }.bind(this));

                    if ((0 !== file.type.indexOf('image')) || (file.size > myDropzone.options.maxThumbnailFilesize)) {
                        // This is not an image, or image is too big to generate a thumbnail
                        myDropzone.emit(
                            'thumbnail',
                            file,
                            router.generate('pim_enrich_default_thumbnail', {mimeType: file.type})
                        );
                    }
                }.bind(this));

                myDropzone.on('success', function (file) {
                    this.setStatus(file);
                    file.previewElement.querySelector('.AknProgress').className = 'AknProgress AknProgress--apply';
                    file.previewElement.querySelector('.AknProgress .AknProgress-bar').style.width = '100%';
                    $(file.previewElement.querySelector('.AknButton.cancel')).addClass('AknButton--hidden');
                    $(file.previewElement.querySelector('.AknButton.delete')).removeClass('AknButton--hidden');
                }.bind(this));

                myDropzone.on('error', function (file, error) {
                    file.previewElement.querySelector('.filename .error.text-danger')
                        .textContent = __(error.error);
                    this.setStatus(file);
                }.bind(this));

                myDropzone.on('sending', function (file) {
                    this.setStatus(file);
                }.bind(this));

                myDropzone.on('queuecomplete', function () {
                    if (myDropzone.getFilesWithStatus(Dropzone.SUCCESS).length > 0) {
                        $importButton.removeClass('AknButton--disabled');
                    }
                    $startButton.addClass('AknButton--disabled');
                }.bind(this));

                /**
                 * Delete/Cancel file handler
                 *
                 * @param {Object} file File object to remve
                 */
                myDropzone.removeFile = function (file) {
                    if (Dropzone.SUCCESS === file.status) {
                        $.ajax({
                            url: router.generate(
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
                                __('pimee_product_asset.mass_upload.error.delete')
                            );
                        });
                    } else {
                        Dropzone.prototype.removeFile.call(this, file);
                    }
                };

                $.get(router.generate('pimee_product_asset_mass_upload_rest_list'))
                    .done(function (response) {
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
                    })
                    .fail(function () {
                        messenger.notificationFlashMessage(
                            'error',
                            __('pimee_product_asset.mass_upload.error.list')
                        );
                    })
                ;

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
                $importButton.addClass('AknButton--disabled');
                this.myDropzone.removeAllFiles(true);
                messenger.notificationFlashMessage(
                    'success',
                    __('pimee_product_asset.mass_upload.success.canceled')
                );
            },

            /**
             * Import uploaded files for asset processing
             */
            importAll: function () {
                $importButton.addClass('AknButton--disabled');
                $.get(router.generate('pimee_product_asset_mass_upload_rest_import'))
                    .done(function (response) {
                        messenger.notificationFlashMessage(
                            'success',
                            __('pimee_product_asset.mass_upload.success.imported')
                        );

                        router.redirectToRoute('pim_enrich_job_tracker_show', {id: response.jobId});
                    }.bind(this))
                    .fail(function () {
                        messenger.notificationFlashMessage(
                            'error',
                            __('pimee_product_asset.mass_upload.error.import')
                        );
                    })
                ;
            },

            /**
             * Change asset status in the grid
             *
             * @param {Object} file
             */
            setStatus: function (file) {
                var statusElement = file.previewElement.querySelector('.dz-status');
                var statusClasses = {
                    'error': 'AknBadge--invalid',
                    'added': 'AknBadge--success',
                    'success': 'AknBadge--success'
                };
                statusElement.classList.add(statusClasses[file.status]);
                var statusKey = 'pimee_product_asset.mass_upload.status.' + file.status;
                statusElement.textContent = __(statusKey);
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
