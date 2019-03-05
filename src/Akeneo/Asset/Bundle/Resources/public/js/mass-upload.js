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
        'pim/template/common/modal-centered'
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
        manageAssetModalTemplate
    ) {
        /**
         * Override to be able to use template root different other than 'div'
         *
         * @param string
         *
         * @returns {*}
         */
        Dropzone.createElement = function (string) {
            const el = $(string);

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
            cancelRedirectionRoute: '',
            importRoute: '',
            entity: null,

            /**
             * Sets the various routes to use.
             *
             * @param {Object} routes
             *
             * @return {exports}
             */
            setRoutes(routes) {
                this.cancelRedirectionRoute = routes.cancelRedirectionRoute;
                this.importRoute = routes.importRoute;

                return this;
            },

            /**
             * Sets the type and ID of the entity the assets will be added to once uploaded.
             *
             * @param {Object} entity
             *
             * @return {exports}
             */
            setEntity(entity) {
                this.entity = entity;

                return this;
            },

            /**
             * Clean up modal
             */
            clearModal() {
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
            shutdown() {
                this.clearModal();

                BaseForm.prototype.shutdown.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.clearModal();

                const modal = new Backbone.BootstrapModal({
                    className: 'modal mass-upload-modal',
                    title: __('pim_title.pimee_product_asset_index'),
                    subtitle: __('pimee_product_asset.mass_upload.menu'),
                    okText: '',
                    content: this.pageTemplate({
                        __
                    }),
                    template: _.template(manageAssetModalTemplate)
                });

                modal.open();

                modal.$el.on('click', '.start:not(.AknButton--disabled)', this.startAll.bind(this));
                modal.$el.on('click', '.remove:not(.AknButton--disabled)', this.cancelAll.bind(this));
                modal.$el.on('click', '.import:not(.AknButton--disabled)', this.importAll.bind(this));
                modal.$el.on('click', '.cancel:not(.AknButton--disabled)', this.cancel.bind(this));

                this.modal = modal;

                $navbarButtons = $('.navbar-buttons');
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
            setStatus(file) {
                const progressBar = $(file.previewElement).find('.AknProgress');
                progressBar.attr('data-status', file.status);
            },

            /**
             * Initialize the dropzone element
             */
            initializeDropzone() {
                const myDropzone = new Dropzone('.mass-upload-dropzone', {
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
                    ).fail((response) => {
                        file.status = Dropzone.ERROR;
                        let message = 'pimee_product_asset.mass_upload.error.filename';
                        if (response.responseJSON) {
                            message = response.responseJSON.error;
                        }
                        file.previewElement.querySelector('.AknFieldContainer-validationError')
                            .textContent = __(message);
                        this.setStatus(file)
                    })
                    .always(() => {
                        $startButton.removeClass('AknButton--disabled');
                        $cancelButton.removeClass('AknButton--hidden');

                        file.previewElement.querySelector('.dz-type').textContent = file.type;
                        this.setStatus(file);
                    });

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
                    const progressBar = file.previewElement.querySelector('.AknProgress');
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
                            .done(function () {
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
                            const mockFile = {
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
            startAll() {
                this.myDropzone.enqueueFiles(this.myDropzone.getFilesWithStatus(Dropzone.ADDED));
            },

            /**
             * Cancel all uploads and delete already uploaded files
             */
            cancelAll() {
                $importButton.addClass('AknButton--disabled');
                this.myDropzone.removeAllFiles(true);
                messenger.notify(
                    'success',
                    __('pimee_product_asset.mass_upload.success.canceled')
                );
            },

            cancel() {
                this.myDropzone.removeAllFiles(true);
                this.clearModal();
                if ('' !== this.cancelRedirectionRoute) {
                    router.redirectToRoute(this.cancelRedirectionRoute);
                }
            },

            /**
             * Import uploaded files for asset processing
             */
            importAll() {
                $importButton.addClass('AknButton--disabled');

                const route = null !== this.entity
                    ? router.generate(this.importRoute, {
                        entityType: this.entity.type,
                        entityIdentifier: this.entity.identifier,
                        attributeCode: this.entity.attributeCode
                    })
                    : router.generate(this.importRoute);

                $.get(route).done(response => {
                    const message = {};
                    if (null === this.entity) {
                        message.notification = 'success';
                        message.content = 'pimee_product_asset.mass_upload.success.imported';
                        message.flash = true;
                    } else {
                        message.info = 'success';
                        message.content = 'pimee_product_asset.mass_upload.success.need_refresh';
                        message.flash = false;
                    }

                    messenger.notify(message.notification, __(message.content), {flash: message.flash});
                    this.clearModal();

                    if (null === this.entity) {
                        router.redirectToRoute('pim_enrich_job_tracker_show', {id: response.jobId});
                    }
                }).fail(() => {
                    messenger.notify(
                        'error',
                        __('pimee_product_asset.mass_upload.error.import')
                    );
                });
            },

            /**
             * Find a dropzone file with filename
             *
             * @param {String} filename
             *
             * @returns {Object|null}
             */
            findFile(filename) {
                return _.findWhere(this.myDropzone.files, {name: filename});
            }
        });
    }
);
