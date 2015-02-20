define(
    ['jquery', 'oro/translator', 'oro/mediator', 'oro/navigation', 'oro/messenger', 'pim/dialog',
     'pim/saveformstate', 'pim/asynctab', 'pim/ui', 'oro/loading-mask'],
    function ($, __, mediator, Navigation, messenger, Dialog, saveformstate, loadTab, UI, LoadingMask) {
        'use strict';
        var initialized = false;
        return function() {
            if (initialized) {
                return;
            }
            initialized = true;
            var setFullHeight = function ($target) {
                if (!$target) {
                    $target = $('body');
                }
                $target.find('.fullheight').filter(':visible').each(function () {
                    $(this).height($('.scrollable-container').height() - $(this).position().top + $('.scrollable-container').position().top);
                });
            };
            var pageInit = function ($target) {
                if (!$target) {
                    $target = $('body');
                    $target.find('form.form-horizontal').each(function() {
                        saveformstate($(this).attr('id'), loadTab);
                    });
                }
                // Place code that we need to run on every page load here

                $target.find('.remove-attribute').each(function () {
                    var target = $(this).parent().find('.icons-container');
                    if (target.length) {
                        $(this).appendTo(target).attr('tabIndex', -1);
                    }
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
                $target.find('.attribute-field.localizable').each(function () {
                    var $iconsContainers = $(this).find('div.controls').find('.icons-container');
                    if (!$iconsContainers.find('i.icon-globe').length) {
                        $iconsContainers.prepend($localizableIcon.clone());
                    }
                });

                UI($target);

                $target.find('a[data-form-toggle]').on('click', function () {
                    $('#' + $(this).attr('data-form-toggle')).show();
                    $(this).hide();
                });

                $target.find('a[data-toggle="tab"]').on('show.bs.tab', function() {
                    loadTab(this);
                });

                setFullHeight($target);
            };

            $(function(){
                $(document).on('tab.loaded', 'form.form-horizontal', function(e, tab) {
                    pageInit($(tab));
                });

                $(document).on('shown', 'a[data-toggle="tab"]', function() {
                    var target = $(this).attr('href');
                    if (target && target !== '#' && target.indexOf('javascript') !== 0) {
                        setFullHeight($(target).parent());
                    }
                });

                var secret = "38384040373937396665";
                var input = "";
                var timer;
                $(document).keyup(function(e) {
                    input += e.which;
                    clearTimeout(timer);
                    timer = setTimeout(function() { input = ""; }, 500);
                    if (input == secret) {
                        $(document.body).addClass('konami');
                    }
                });

                // DELETE request for delete buttons
                $(document).on('click', '[data-dialog]', function () {
                    var $el      = $(this),
                        message  = $el.data('message'),
                        title    = $el.data('title'),
                        doAction = function () {

                            var loadingMask = new LoadingMask();
                            loadingMask.render().$el.appendTo($(document.body)).css({ 'position': 'absolute', 'top': '0px', 'left': '0px', 'width': '100%', 'height': '100%'});
                            loadingMask.show();

                            $.ajax({
                                url: $el.attr('data-url'),
                                type: 'POST',
                                headers: { accept:'application/json' },
                                data: { _method: $el.data('method') },
                                success: function() {
                                    loadingMask.hide().$el.remove();
                                    var navigation = Navigation.getInstance();
                                    var targetUrl = '#url=' + $el.attr('data-redirect-url');
                                    // If already on the desired page, make sure it is refreshed
                                    if (targetUrl === window.location.hash) {
                                        navigation.navigate(targetUrl.substr(0, targetUrl.length -1));
                                    }
                                    navigation.navigate(targetUrl, { trigger: true });
                                    navigation.addFlashMessage('success', $el.attr('data-success-message'));
                                },
                                error: function(xhr) {
                                    loadingMask.hide().$el.remove();
                                    messenger.notificationFlashMessage(
                                        'error',
                                        (xhr.responseJSON && xhr.responseJSON.message) ?
                                            xhr.responseJSON.message :
                                            $el.attr('data-error-message'));
                                }
                            });
                        };
                    $el.off('click');
                    if ($el.data('dialog') === 'confirm') {
                        Dialog.confirm(message, title, doAction);
                    } else {
                        Dialog.alert(message, title);
                    }

                    return false;
                });

                pageInit();
            });
            mediator.bind('hash_navigation_request:complete pim:reinit', function () {
                pageInit();
            });
        };
    }
);
