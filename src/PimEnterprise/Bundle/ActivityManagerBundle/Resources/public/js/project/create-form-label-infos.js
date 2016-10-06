'use strict';

/**
 * Extension of the Form Creation for project.
 * This extension checks the label field and displays errors if any.
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
            className: 'label-errors',
            maxLengthLabel: null,
            template: _.template(template),
            isHidden: true,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.maxLengthLabel = config.config.maxLength;
            },

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
                    message: __('activity_manager.project.label_maxlength', {max: this.maxLengthLabel})
                }))
            },

            /**
             * Method called on model updated. Check if the model's label respects the max length.
             * Displays a message if too long and triggers an event to toggle the "Next" button of the modal.
             *
             * @param {object} model
             */
            onModelUpdate: function (model) {
                var label = model.get('label');
                var labelTooLong = (label.length > this.maxLengthLabel);

                this.isHidden = !labelTooLong;
                this.getRoot().trigger(
                    'grid:view-selector:create-project:update-field-value',
                    'label',
                    !labelTooLong
                );

                this.render();
            }
        });
    }
);
