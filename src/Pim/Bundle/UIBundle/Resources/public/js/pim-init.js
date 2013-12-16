define(
    ['jquery', 'oro/translator', 'oro/mediator', 'oro/navigation', 'oro/messenger', 'pim/dialog', 'oro/loading-mask',
     'pim/initselect2', 'pim/saveformstate', 'bootstrap', 'bootstrap.bootstrapswitch', 'bootstrap-tooltip'],
    function ($, __, mediator, Navigation, messenger, Dialog, LoadingMask, initSelect2, saveformstate) {
        'use strict';
        var initialized = false;
        return function() {
            if (initialized) {
                return;
            }
            initialized = true;
            function loadTab(tab) {
                var $tab = $(tab);
                var target = $tab.attr('href');
                if (!target || target === '#' || target.indexOf('javascript') === 0) {
                    return;
                }
                var $target = $(target);

                if (!$target.attr('data-loaded') && $target.attr('data-url')) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo($('#container'));
                    loadingMask.show();

                    $.get($target.attr('data-url'), function(data) {
                        $target.html(data);
                        $target.attr('data-loaded', 1);
                        loadingMask.hide();
                        loadingMask.$el.remove();
                        $target.closest('form').trigger('tab.loaded');
                        pageInit($target);
                    });
                }
            }
            function pageInit($target) {
                if (!$target) {
                    $target = $('body');
                    $target.find('form.form-horizontal').each(function() {
                        saveformstate($(this).attr('id'), loadTab);
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
