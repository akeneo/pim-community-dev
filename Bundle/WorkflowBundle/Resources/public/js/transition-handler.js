define(['jquery', 'oro/messenger', 'oro/translator', 'oro/navigation'],
function($, messenger, __, Navigation) {
    'use strict';

    var navigation = Navigation.getInstance();
    var performTransition = function(element) {
        $.getJSON(element.data('href'))
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

                /** Handle redirectUrl result parameter for RedirectPostAction */
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
    return function(e) {
        e.preventDefault();

        var element = $(this);
        if (element.data('has-attributes')) {
            require(['oro/dialog-widget'],
            function(DialogWidget) {
                var transitionFormWidget = new DialogWidget({
                    title: element.data('transition-label') || element.html(),
                    /** TODO: replace with URL of transition form widget **/
                    el: $('<div><span>Transition form stub</span><div class="widget-actions"><button data-action-name="execute">Execute</button></div></div>'),
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
                transitionFormWidget.on('transitionFormSaved', function() {
                    transitionFormWidget.remove();
                    performTransition(element)
                });
                transitionFormWidget.render();

                /** TODO: Remove this stub handler **/
                transitionFormWidget.getAction('form_submit', 'adopted', function(action) {
                    action.on('click', function() {
                        transitionFormWidget.trigger('transitionFormSaved');
                    });
                });
            });
        } else {
            performTransition(element);
        }
    }
});