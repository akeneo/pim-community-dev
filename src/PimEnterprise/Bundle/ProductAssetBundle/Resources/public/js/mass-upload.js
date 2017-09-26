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
        'pimee/template/asset/mass-upload',
        'pimee/template/asset/mass-upload-row',
        'pim/form-builder',
        'pim/common/breadcrumbs',
        'pim/form',
        'oro/mediator'
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
        rowTemplate,
        formBuilder,
        Breadcrumbs,
        BaseForm,
        mediator
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

        return BaseForm.extend({
            myDropzone: null,
            pageTemplate: _.template(pageTemplate),
            rowTemplate: _.template(rowTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function() {
                mediator.once('route_start', this.clearModal.bind(this));

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Destroys previous modal
             */
            clearModal: function() {
                if (this.modal) {
                    this.modal.close();
                    this.modal.remove();
                }

                $('.mass-upload-modal').remove();
                this.$el.empty();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.clearModal();

                const modal = new Backbone.BootstrapModal({
                    className: 'modal modal--fullPage modal--topButton mass-upload-modal',
                    allowCancel: false,
                    content: this.pageTemplate({
                      __,
                      subTitleLabel: 'Assets',
                      titleLabel: 'Upload assets'
                    })
                });

                modal.open();
                modal.$el.find('.modal-body').addClass('modal-body-full');

                modal.$el.on('click', '.start:not(.AknButton--disabled)', this.startAll.bind(this));
                modal.$el.on('click', '.remove:not(.AknButton--disabled)', this.cancelAll.bind(this));
                modal.$el.on('click', '.import:not(.AknButton--disabled)', this.importAll.bind(this));
                modal.$el.on('click', '.cancel:not(.AknButton--disabled)', () => {
                    this.myDropzone.destroy();
                    modal.close();
                    modal.remove();
                    router.redirectToRoute('pimee_product_asset_index')
                });

                this.modal = modal;

                $navbarButtons = $('.AknTitleContainer-rightButtons');
                $importButton = $navbarButtons.find('.import');
                $startButton = $navbarButtons.find('.start');
                $cancelButton = $navbarButtons.find('.remove');

                this.initializeDropzone();

                return this;
            },

            /**
             * Set the status as a data attribute for each asset
             * @param {Object} file File object for an asset
             */
            setStatus: function(file) {
                const progressBar = $(file.previewElement).find('.AknProgress')
                progressBar.attr('data-status', file.status);
            },

            /**
             * Initialize the dropzone element
             */
            initializeDropzone: function () {
                var myDropzone = new Dropzone('.mass-upload-dropzone', {
                    url: router.generate('pimee_product_asset_rest_upload'),
                    thumbnailWidth: 70,
                    thumbnailHeight: 70,
                    parallelUploads: 4,
                    previewTemplate: this.rowTemplate(),
                    autoQueue: false,
                    previewsContainer: '.mass-upload-container',
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
                        file.previewElement.querySelector('.dz-type').textContent = file.type;
                        this.setStatus(file);
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
                    const progressBar = file.previewElement.querySelector('.AknProgress')
                    progressBar.className = 'AknProgress AknProgress--apply';
                    this.setStatus(file);
                    file.previewElement.querySelector('.AknProgress .AknProgress-bar').style.width = '100%';
                    $(file.previewElement.querySelector('.AknButton.cancel')).addClass('AknButton--hidden');
                    $(file.previewElement.querySelector('.AknButton.delete')).removeClass('AknButton--hidden');
                }.bind(this));

                myDropzone.on('error', function (file, error) {
                    file.previewElement.querySelector('.filename .error.text-danger')
                        .textContent = __(error.error);
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
                            messenger.notify(
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
                        messenger.notify(
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
                messenger.notify(
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
                        messenger.notify(
                            'success',
                            __('pimee_product_asset.mass_upload.success.imported')
                        );

                        router.redirectToRoute('pim_enrich_job_tracker_show', {id: response.jobId});
                    }.bind(this))
                    .fail(function () {
                        messenger.notify(
                            'error',
                            __('pimee_product_asset.mass_upload.error.import')
                        );
                    })
                ;
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
