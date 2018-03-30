define(
    [
    'jquery',
    'underscore',
    'backbone',
    'oro/mediator',
    'oro/messenger',
    'pim/dialog',
    'pim/saveformstate',
    'pim/asynctab',
    'pim/ui',
    'oro/loading-mask',
    'pim/router',
    'require-polyfill'
],
    function (
        $,
        _,
        Backbone,
        mediator,
        messenger,
        Dialog,
        saveformstate,
        loadTab,
        UI,
        LoadingMask,
        router
    ) {
        'use strict';
        var initialized = false;

        return function () {

            if (initialized) {
                return;
            }
            initialized = true;
            var setFullHeight = function ($target) {
                if (!$target) {
                    $target = $('body');
                }
            };
            var pageInit = function ($target) {
                if (!$target) {
                    $target = $('body');
                    $target.find('form.form-horizontal, [data-saveformstate]').each(function () {
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

                var $localizableIcon = $('<i>', {
                    'class': 'icon-globe',
                    'attr': {
                        'data-original-title': _.__('Localized value'),
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

                $target.find('a[data-toggle="tab"]').on('show.bs.tab', function () {
                    loadTab(this);
                });

                //Flash messages:
                if (window.flashMessages) {
                    _.each(window.flashMessages, function (messages, type) {
                        _.each(messages, function (message) {
                            messenger.notify(
                                type,
                                message
                            );
                        });
                    });
                }
                window.flashMessages = [];

                setFullHeight($target);
            };

            $(function () {
                $(document).on('tab.loaded', 'form.form-horizontal, [data-saveformstate]', function (e, tab) {
                    pageInit($(tab));
                });

                $(document).on('shown', 'a[data-toggle="tab"]', function () {
                    var target = $(this).attr('href');
                    if (target && target !== '#' && target.indexOf('javascript') !== 0) {
                        setFullHeight($(target).parent());
                    }
                });

                var secret = '38384040373937396665';
                var input = '';
                var timer;
                $(document).on('keyup', function (e) {
                    input += e.which;
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        input = '';
                    }, 500);
                    if (input === secret) {
                        $(document.body).addClass('konami');
                    }
                });

                // DELETE request for delete buttons
                $(document).on('click', '[data-dialog]', function () {
                    const $el      = $(this);
                    const message  = $el.data('message');
                    const title    = $el.data('title');
                    const subTitle = $el.data('subtitle');
                    const buttonLabel = $el.data('buttonlabel');

                    const doAction = function () {
                        const loadingMask = new LoadingMask();
                        loadingMask.render().$el.appendTo($(document.body)).css(
                            {
                                'position': 'absolute',
                                'top': '0px',
                                'left': '0px',
                                'width': '100%',
                                'height': '100%'
                            }
                        );
                        loadingMask.show();

                        $.ajax({
                            url: $el.attr('data-url'),
                            type: 'POST',
                            headers: { accept: 'application/json' },
                            data: { _method: $el.data('method') },
                            success: function () {
                                loadingMask.hide().$el.remove();
                                const targetUrl = $el.attr('data-redirect-url');
                                router.redirect(targetUrl, {trigger: true});
                                messenger.notify('success', $el.attr('data-success-message'));
                            },
                            error: function (xhr) {
                                loadingMask.hide().$el.remove();
                                messenger.notify(
                                    'error',
                                    (xhr.responseJSON && xhr.responseJSON.message) ?
                                        xhr.responseJSON.message :
                                        $el.attr('data-error-message'));
                            }
                        });
                    };
                    $el.off('click');

                    if ($el.data('dialog') === 'confirm') {
                        Dialog.confirm(message, title, doAction, subTitle, '', buttonLabel);
                    } else if ($el.data('dialog') === 'delete') {
                        Dialog.confirmDelete(message, title, doAction, subTitle);
                    } else {
                        Dialog.alert(message, title, subTitle);
                    }

                    return false;
                });

                pageInit();
            });
            mediator.on('route_complete pim:reinit', function () {
                pageInit();
            });
        };
    }
);
