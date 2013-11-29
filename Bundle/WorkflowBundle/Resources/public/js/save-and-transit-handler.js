define(['jquery', 'oro/mediator', 'routing', 'oro/buttons-widget'],
function ($, mediator, routing, ButtonsWidget) {
    'use strict';

    /**
     * Save and transit button click handler
     *
     * @export  oro/workflow-save-and-transit-handler
     * @class   oro.WorkflowSaveAndTransitHandler
     */
    return function() {
        var saveBtn = $(this);
        // Modify form to stay on edit page after submit
        var form = saveBtn.closest('form');
        var actionInput = form.find('input[name="input_action"]');
        actionInput.val('save_and_stay');
        var formId = form.prop('id');

        // On form submit response check for errors
        mediator.once('hash_navigation_request:refresh', function (navigation) {
            var content = $(navigation.selectorCached.container);
            var hasErrors = content.find('.alert-error, .validation-error').length > 0;
            if (!hasErrors) {
                var idRegexp = /update\/(\d+).*/;
                var responseForm = content.find('#' + formId);
                var elementIdMatch = idRegexp.exec(responseForm.prop('action'));
                if (elementIdMatch.length > 1) {
                    // In case when no errors occurred load transitions for created entity
                    var containerEl = $('<div class="hidden invisible"/>');
                    $('body').append(containerEl);
                    var transitionsWidget = new ButtonsWidget({
                        'el': containerEl,
                        'elementFirst': false,
                        'url': routing.generate('oro_workflow_widget_buttons_entity', {
                            'entityId': elementIdMatch[1],
                            'entityClass': saveBtn.data('entity-class')
                    })
                    });
                    transitionsWidget.on('renderComplete', function(el) {
                        // Try to execute required transition
                        var transition = el.find('#transition-' + saveBtn.data('workflow') + '-' + saveBtn.data('transition'));
                        if (transition.length) {
                            transition.on('transitionHandlerInitialized', function() {
                                transition.data('executor').call();
                                containerEl.remove();
                            });
                        }
                    });
                    transitionsWidget.render();
                }
            }
        });
        form.submit();
    };
});