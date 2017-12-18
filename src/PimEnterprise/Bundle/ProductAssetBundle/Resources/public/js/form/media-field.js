'use strict';

/**
 * This file adds the logic for the creation popin of the asset creation.
 *
 * @deprecated This is to drop completely and replace by real BaseForm components.
 */
define(
    [
        'jquery',
        'oro/translator',
        'oro/mediator',
        'routing',
        'pimee/productasset/uploader',
        'pim/router',
        'bootstrap.bootstrapswitch'
    ],
    function ($, __, mediator, Routing, Uploader, router) {
        return {
            initialize() {
                mediator.on('dialog:open:after', this.initializeDropzone.bind(this));
            },

            initializeDropzone() {
                new Uploader();
                let dialog     = $('div.ui-dialog[aria-describedby="pimee_product_asset_create"]');
                let fileInput  = dialog.find('input[type=file]');
                let codeInput  = dialog.find('.code-field input[type="text"]');
                let saveBtn    = $('<button type="button" class="btn btn-primary">Save</button>');
                let oldSaveBtn = dialog.find('.btn-primary');

                if (oldSaveBtn) {
                    oldSaveBtn.remove();
                }
                dialog.find('div.ui-dialog-buttonset').prepend(saveBtn);

                dialog.find('.has-switch').on('switch-change', function(event, data) {
                    if (true === data.value) {
                        dialog.find('.reference-field').hide('fast');
                    } else {
                        dialog.find('.reference-field').show('fast');
                    }
                });

                fileInput.change((event) => {
                    const fileInput = event.currentTarget;
                    codeInput.val(this.sanitizeFileName(fileInput.value));
                });
                saveBtn.click((event) => {
                    this.checkNextCode(event, dialog, codeInput);
                });
                codeInput.focusout((event) => {
                    this.checkNextCode(event, dialog, codeInput);
                });
                dialog.keypress((event) => {
                    if (event.keyCode === $.ui.keyCode.ENTER) {
                        this.checkNextCode(event, dialog, codeInput);
                    }
                });
            },

            checkNextCode: function(event, dialog, codeInput) {
                if (typeof event.bubbles === 'undefined') {
                    return false;
                }
                let iconDiv = codeInput.closest('.AknFieldContainer').find('.icons-container');
                let icon = $('<i/>')
                    .addClass('AknIconButton AknIconButton--important icon-warning-sign validation-tooltip');

                iconDiv.empty();
                icon.tooltip('destroy');

                if (!codeInput.val()) {
                    iconDiv.append(icon);
                    icon.tooltip({
                        title: __('pimee_product_asset.form.asset.not_empty'),
                        placement: 'right'
                    });

                    return false;
                }

                if (!codeInput.val().match('^[a-zA-Z0-9_]+$')) {
                    iconDiv.append(icon);
                    icon.tooltip({
                        title: __('pimee_product_asset.form.asset.alpha_numeric_plus_underscore'),
                        placement: 'right'
                    });

                    return false;
                }

                $.ajax({
                    url: Routing.generate('pimee_product_asset_next_code', { code: codeInput.val() })
                }).done(function (data) {
                    if (typeof data.nextCode !== 'undefined') {
                        codeInput.val(data.nextCode);
                        iconDiv.empty().append(icon);
                        icon.tooltip({
                            title: __('pimee_product_asset.form.asset.unique'),
                            placement: 'right'
                        });
                    } else if ('click' === event.type || 'keypress' === event.type) {
                        const dataFields = ($('#pimee_product_asset_create').serializeArray());
                        let formData = new FormData();

                        dataFields.forEach((dataField) => {
                            formData.append(dataField.name, dataField.value);
                        });

                        let input = $(dialog).find('input[type="file"]').get(0);
                        if (undefined !== input.files[0]) {
                            formData.append('pimee_product_asset_create[reference_file][uploadedFile]', input.files[0]);
                        }

                        $.ajax({
                            url: Routing.generate('pimee_product_asset_create'),
                            type: 'post',
                            data: formData,
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function (data) {
                                router.redirectToRoute(data.route, data.params);
                                dialog.remove();
                                $('.AknLoadingMask').hide();
                            }
                        });

                        event.stopPropagation();
                    }
                });
            },

            sanitizeFileName: function (str) {
                return str
                    .replace(/\\/g, '/')
                    .replace(/.*\//, '')
                    .replace(/\.[^/.]+$/, '')
                    .replace(/[^A-Za-z0-9_]/g, '_');
            }
        }
    }
);
