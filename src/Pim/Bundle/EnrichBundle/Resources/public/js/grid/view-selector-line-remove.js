'use strict';

/**
 * Extension module for a dropdown line in the Datagrid View Selector.
 * Displays a button beside the attached view in the list to allow the user
 * to remove the view.
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/grid/view-selector/line/remove',
        'pim/dialog',
        'routing',
        'pim/remover/datagrid-view',
        'oro/messenger'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        Dialog,
        Routing,
        DatagridViewRemover,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(template),
            tagName: 'span',
            className: 'remove-button pull-right',
            events: {
                'click [data-action="prompt-deletion"]': 'promptDeletion'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    hidden: 0 === this.getParent().datagridView.id
                }));
            },

            /**
             * Prompt the datagrid view deletion modal.
             */
            promptDeletion: function () {
                this.getRoot().trigger('grid:view-selector:close-selector');

                Dialog.confirm(
                    __('grid.view_selector.confirmation.remove'),
                    __('grid.view_selector.confirmation.delete'),
                    function () {
                        this.removeView();
                    }.bind(this)
                );
            },

            /**
             * Remove the Datagrid View of this line and triggers an event to the parent.
             */
            removeView: function () {
                var lineView = this.getParent().datagridView;

                DatagridViewRemover.remove(lineView)
                    .done(function () {
                        this.getRoot().trigger('grid:view-selector:view-removed');
                    }.bind(this))
                    .fail(function (response) {
                        messenger.notificationFlashMessage('error', response.responseJSON);
                    });
            }
        });
    }
);
