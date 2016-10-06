'use strict';

/**
 * Extension of the Form Creation for project.
 * This extension checks the due date field and displays errors if any.
 *
 * @author Adrien Petremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!activity-manager/templates/create-project/field-error'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'due-date-errors',
            template: _.template(template),
            isHidden: true,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getParent(),
                    'grid:view-selector:create-project:model-updated',
                    this.onModelUpdate.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    isHidden: this.isHidden,
                    message: __('activity_manager.project.due_date_past')
                }))
            },

            /**
             * Method called on model updated. Check if the model's due date isn't in the past.
             * Displays a message if in the past and triggers an event to toggle the "Next" button of the modal.
             *
             * @param {object} model
             */
            onModelUpdate: function (model) {
                var dueDateInPast = false;

                if (model.get('due_date')) {
                    var dueDate = new Date(model.get('due_date'));
                    var today = new Date().setHours(0, 0, 0, 0);
                    dueDateInPast = today > dueDate;
                }

                this.isHidden = !dueDateInPast;
                this.getRoot().trigger(
                    'grid:view-selector:create-project:update-field-value',
                    'due_date',
                    !dueDateInPast
                );

                this.render();
            }
        });
    }
);
