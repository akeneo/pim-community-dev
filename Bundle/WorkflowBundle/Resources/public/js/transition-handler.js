define(['jquery', 'oro/messenger', 'oro/translator', 'oro/navigation', 'oro/modal'],
function($, messenger, __, Navigation, Modal) {
    'use strict';

    var navigation = Navigation.getInstance();
    var performTransition = function(element, data) {
        $.getJSON(element.data('transition-url'), data ? {'data': data} : null)
            .done(function(response) {
                var doRedirect = function(redirectUrl) {
                    if (navigation) {
                        navigation.setLocation(redirectUrl);
                    } else {
                        window.location.href = redirectUrl;
                    }
                };
                var doReload = function() {
                    if (navigation) {
                        navigation.loadPage();
                    } else {
                        window.location.reload();
                    }
                };

                /** Handle redirectUrl result parameter for RedirectAction */
                element.one('transitions_success', function(e, response) {
                    if (response.workflowItem
                        && response.workflowItem.result
                        && response.workflowItem.result.redirectUrl
                        ) {
                        e.stopImmediatePropagation();
                        doRedirect(response.workflowItem.result.redirectUrl);
                    }
                });
                /** Handle redirect-to-workflow element data parameter */
                element.one('transitions_success', function(e, response) {
                    var workflowItemId = null;
                    if (response.workflowItem && response.workflowItem.id) {
                        workflowItemId = response.workflowItem.id;
                    }
                    var needRedirect = element.data('redirect-to-workflow');
                    if (needRedirect && workflowItemId) {
                        e.stopImmediatePropagation();
                        var redirectUrl = Routing.generate(
                            'oro_workflow_step_edit',
                            {id: workflowItemId}
                        );
                        doRedirect(redirectUrl);
                    }
                });
                /** By default reload page */
                element.one('transitions_success', doReload);
                element.trigger('transitions_success', [response]);
            })
            .fail(function(jqxhr, textStatus, error) {
                element.one('transitions_failure', function() {
                    messenger.notificationFlashMessage('error', __('Could not perform transition'));
                });
                element.trigger('transitions_failure', [jqxhr, textStatus, error]);
            });
    };

    /**
     * Transition button click handler
     *
     * @export  oro/workflow-transition-handler
     * @class   oro.WorkflowTransitionHandler
     */
    return function() {
        var element = $(this);
        if (element.data('_in-progress')) {
            return;
        }
        element.data('_in-progress', true);
        var resetInProgress = function() {
            element.data('_in-progress', false);
        };
        element.one('transitions_success', resetInProgress);
        element.one('transitions_failure', resetInProgress);
        if (element.data('dialog-url')) {
            require(['oro/dialog-widget'],
            function(DialogWidget) {
                var transitionFormWidget = new DialogWidget({
                    title: element.data('transition-label') || element.html(),
                    url: element.data('dialog-url'),
                    stateEnabled: false,
                    incrementalPosition: false,
                    loadingMaskEnabled: false,
                    dialogOptions: {
                        modal: true,
                        resizable: false,
                        width: 475,
                        autoResize: true
                    }
                });
                transitionFormWidget.on('renderComplete', resetInProgress);
                transitionFormWidget.on('formSave', function(data) {
                    transitionFormWidget.remove();
                    performTransition(element, data);
                });
                transitionFormWidget.render();
            });
        } else {
            var message = element.data('message');
            if (message) {
                var confirm = new Modal({
                    title: element.data('transition-label'),
                    content: message
                });
                confirm.on('ok', function() {
                    performTransition(element);
                });
                confirm.open();
            } else {
                performTransition(element);
            }
        }
    }
});