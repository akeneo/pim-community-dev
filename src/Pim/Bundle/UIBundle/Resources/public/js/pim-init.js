require(
    ['jquery', 'oro/translator', 'oro/mediator', 'oro/navigation', 'pim/dialog', 'pim/initselect2', 
     'bootstrap', 'bootstrap.bootstrapswitch', 'bootstrap-tooltip', 'jquery.slimbox'],
    function ($, __, mediator, Navigation, Dialog, initSelect2) {
        'use strict';

        function init() {
            // Place code that we need to run on every page load here

            $('.remove-attribute').each(function () {
                var target = $(this).parent().find('.icons-container').first();
                if (target.length) {
                    $(this).appendTo(target).attr('tabIndex', -1);
                }
            });

            // Apply Select2
            initSelect2();

            // Apply bootstrapSwitch
            $('.switch:not(.has-switch)').bootstrapSwitch();

            // Initialize tooltip
            $('[data-toggle="tooltip"]').tooltip();

            // Activate a form tab
            $('li.tab.active a').each(function () {
                var paneId = $(this).attr('href');
                $(paneId).addClass('active');
            });

            // Toogle accordion icon
            $('.accordion').on('show hide', function (e) {
                $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('icon-collapse-alt icon-expand-alt');
            });

            $('#attribute-buttons').find('.dropdown-menu').click(function (e) {
                e.stopPropagation();
            });

            $('#default_channel').change(function () {
                mediator.trigger('scopablefield:changescope', $(this).val());
            });

            $('.dropdown-menu.channel a').click(function (e) {
                e.preventDefault();
                mediator.trigger('scopablefield:' + $(this).data('action'));
            });

            // Save and restore activated form tabs and groups
            function saveFormState() {
                var activeTab   = $('#form-navbar').find('li.active').find('a').attr('href'),
                    activeGroup = $('.tab-groups').find('li.tab.active').find('a').attr('href');

                if (activeTab) {
                    sessionStorage.activeTab = activeTab;
                }

                if (activeGroup) {
                    sessionStorage.activeGroup = activeGroup;
                }
            }

            function restoreFormState() {
                if (sessionStorage.activeTab) {
                    var $activeTab = $('a[href=' + sessionStorage.activeTab + ']');
                    if ($activeTab.length && !$('.loading-mask').is(':visible')) {
                        $activeTab.tab('show');
                        sessionStorage.removeItem('activeTab');
                    }
                }

                if (sessionStorage.activeGroup) {
                    var $activeGroup = $('a[href=' + sessionStorage.activeGroup + ']');
                    if ($activeGroup.length && !$('.loading-mask').is(':visible')) {
                        $activeGroup.tab('show');
                        sessionStorage.removeItem('activeGroup');
                    }
                }
            }

            if (typeof Storage !== 'undefined') {
                restoreFormState();

                $('form.form-horizontal').on('submit', saveFormState);
                $('#locale-switcher').find('a').on('click', saveFormState);
            }

            // Initialize slimbox
            if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                $("a[rel^='slimbox']").slimbox({
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
            $('.attribute-field.translatable').each(function () {
                $(this).find('div.controls').find('.icons-container').append($localizableIcon.clone().tooltip());
            });

            $('form').on('change', 'input[type="file"]', function () {
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

            $('[data-form-toggle]').on('click', function () {
                $('#' + $(this).attr('data-form-toggle')).show();
                $(this).hide();
            });
        }

        $(function () {
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
            $(document).on('click', 'a[data-dialog]', function () {
                var $el      = $(this),
                    message  = $el.data('message'),
                    title    = $el.data('title'),
                    doAction = function () {
                        $.ajax({
                            url: $el.attr('data-url'),
                            type: 'POST',
                            data: { _method: $el.data('method') },
                            success: function() {
                                var navigation = Navigation.getInstance();
                                navigation.navigate("#url=" + $el.attr("data-redirect-url"), { trigger: true });
                                navigation.addFlashMessage('success', $el.attr('data-success-message'));
                            },
                            error: function(xhr) {
                                Dialog.alert($el.attr("data-error-message"), $el.attr("data-error-title"));
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

            init();
        });

        mediator.bind("hash_navigation_request:complete", function () {
            init();
        });
    }
);
