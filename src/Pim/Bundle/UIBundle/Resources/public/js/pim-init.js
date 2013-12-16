define(
    ['jquery', 'oro/translator', 'oro/mediator', 'oro/navigation', 'oro/messenger', 'pim/dialog', 'oro/loading-mask',
     'pim/initselect2', 'pim/saveformstate', 'bootstrap', 'bootstrap.bootstrapswitch', 'bootstrap-tooltip', 'jquery.slimbox'],
    function ($, __, mediator, Navigation, messenger, Dialog, LoadingMask, initSelect2, saveformstate) {
        'use strict';
        var initialized = false;
        return function() {
            if (initialized) {
                return;
            }
            initialized = true;
            function loadTab(tab) {
                var $target = $(tab.getAttribute('href'));
                if (!$target.attr('data-loaded') && $target.attr('data-url')) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo($('#container'));
                    loadingMask.show();

                    $.get($target.attr('data-url'), function(data) {
                        $target.html(data);
                        $target.attr('data-loaded', 1);
                        loadingMask.hide();
                        loadingMask.$el.remove();
                        pageInit($target);
                    });
                }
            }
            function pageInit($target) {
                if (!$target) {
                    $target = $('body');
                    $target.find('form.form-horizontal').each(function() {
                        saveformstate($(this).attr('id'));
                    });
                }
                // Place code that we need to run on every page load here

                $target.find('.remove-attribute').each(function () {
                    var target = $(this).parent().find('.icons-container').first();
                    if (target.length) {
                        $(this).appendTo(target).attr('tabIndex', -1);
                    }
                });

                // Apply Select2
                initSelect2.init($target);

                // Apply bootstrapSwitch
                $target.find('.switch:not(.has-switch)').bootstrapSwitch();

                // Initialize tooltip
                $target.find('[data-toggle="tooltip"]').tooltip();

                // Initialize popover
                $target.find('[data-toggle="popover"]').popover();

                // Activate a form tab
                $target.find('li.tab.active a').each(function () {
                    var paneId = $(this).attr('href');
                    $(paneId).addClass('active');
                });

                // Toogle accordion icon
                $target.find('.accordion').on('show hide', function (e) {
                    $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('icon-collapse-alt icon-expand-alt');
                });

                // Initialize slimbox
                if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                    $target.find('a[rel^="slimbox"]').slimbox({
                        overlayOpacity: 0.3
                    }, null, function (el) {
                        return (this === el) || ((this.rel.length > 8) && (this.rel === el.rel));
                    });
                }

                var $localizableIcon = $('<i>', {
                    'class': 'icon-globe',
                    'attr': {
                        'data-original-title': __('Localized value'),
                        'data-toggle': 'tooltip',
                        'data-placement': 'right'
                    }
                });
                $target.find('.attribute-field.translatable').each(function () {
                    $(this).find('div.controls').find('.icons-container').eq(0).prepend($localizableIcon.clone().tooltip());
                });

                $target.find('form').on('change', 'input[type="file"]', function () {
                    var $input          = $(this),
                        filename        = $input.val().split('\\').pop(),
                        $zone           = $input.parent(),
                        $info           = $input.siblings('.upload-info').first(),
                        $filename       = $info.find('.upload-filename'),
                        $removeBtn      = $input.siblings('.remove-upload'),
                        $removeCheckbox = $input.siblings('input[type="checkbox"]'),
                        $preview        = $info.find('.upload-preview');

                    if ($preview.prop('tagName').toLowerCase() !== 'i') {
                        var iconClass = $zone.hasClass('image') ? 'icon-camera-retro' : 'icon-file';
                        $preview.replaceWith($('<i>', { 'class': iconClass + ' upload-preview'}));
                        $preview = $info.find('.upload-preview');
                    }

                    if (filename) {
                        $filename.html(filename);
                        $zone.removeClass('empty');
                        $preview.removeClass('empty');
                        $removeBtn.removeClass('hide');
                        $input.addClass('hide');
                        $removeCheckbox.removeAttr('checked');
                    } else {
                        $filename.html($filename.attr('data-empty-title'));
                        $zone.addClass('empty');
                        $preview.addClass('empty');
                        $removeBtn.addClass('hide');
                        $input.removeAttr('disabled').removeClass('hide');
                        $removeCheckbox.attr('checked', 'checked');
                    }
                });

                $target.find('a[data-form-toggle]').on('click', function () {
                    $('#' + $(this).attr('data-form-toggle')).show();
                    $(this).hide();
                });

                $target.find('a[data-toggle="tab"]').on('show.bs.tab', function() {
                    loadTab(this);
                });
            }

            $(function(){
                if ($.isPlainObject($.uniform)) {
                    $.uniform.restore();
                }

                $(document).on('uniformInit', function () {
                    $.uniform.restore();
                });

                $(document).on('click', '.remove-upload', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $input = $(this).siblings('input[type="file"]').first();
                    $input.wrap('<form>').closest('form').get(0).reset();
                    $input.unwrap().trigger('change');
                });

                $(document).on('mouseover', '.upload-zone:not(.empty)', function() {
                    $('input[type="file"]', $(this)).attr('disabled', 'disabled');
                }).on('mouseout', '.upload-zone:not(.empty)', function() {
                    $('input[type="file"]', $(this)).removeAttr('disabled');
                });

                // DELETE request for delete buttons
                $(document).on('click', '[data-dialog]', function () {
                    var $el      = $(this),
                        message  = $el.data('message'),
                        title    = $el.data('title'),
                        doAction = function () {
                            $.ajax({
                                url: $el.attr('data-url'),
                                type: 'POST',
                                headers: {accept:'application/json'},
                                data: { _method: $el.data('method') },
                                success: function() {
                                    var navigation = Navigation.getInstance();
                                    navigation.navigate('#url=' + $el.attr('data-redirect-url'), { trigger: true });
                                    navigation.addFlashMessage('success', $el.attr('data-success-message'));
                                },
                                error: function(xhr) {
                                    messenger.notificationFlashMessage(
                                        'error',
                                        (xhr.responseJSON && xhr.responseJSON.message) ?
                                            xhr.responseJSON.message :
                                            $el.attr('data-error-message'));
                                }
                            });
                        };
                    $el.off('click');
                    if ($el.data('dialog') ===  'confirm') {
                        Dialog.confirm(message, title, doAction);
                    } else {
                        Dialog.alert(message, title);
                    }

                    return false;
                });

                pageInit();
            });
            mediator.bind('hash_navigation_request:complete', function () {
                pageInit();
            });
        };
    }
);
