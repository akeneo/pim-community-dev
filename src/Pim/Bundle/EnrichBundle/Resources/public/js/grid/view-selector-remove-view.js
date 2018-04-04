'use strict';

/**
 * Remove extension for the Datagrid View Selector.
 * It displays a button near the selector to allow the user to remove the current view.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/grid/view-selector/remove-view',
        'pim/dialog',
        'pim/user-context',
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
        UserContext,
        DatagridViewRemover,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(template),
            tagName: 'span',
            className: 'remove-button',
            events: {
                'click .remove': 'promptDeletion'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if ('view' !== this.getRoot().currentViewType ||
                    this.getRoot().currentView.id === 0 ||
                    UserContext.get('meta').id !== this.getRoot().currentView.owner_id
                ) {
                    this.$el.html('');

                    return this;
                }

                this.$el.html(this.template({
                    label: __('pim_datagrid.view_selector.remove')
                }));

                this.$('[data-toggle="tooltip"]').tooltip();

                return this;
            },

            /**
             * Prompt the datagrid view deletion modal.
             */
            promptDeletion: function (event) {
                event.stopPropagation();

                Dialog.confirm(
                    __('pim_datagrid.view_selector.confirmation.remove'),
                    __('pim_common.delete'),
                    function () {
                        this.removeView(this.getRoot().currentView);
                    }.bind(this)
                );
            },

            /**
             * Remove the current Datagrid View and triggers an event to the parent.
             *
             * @param {Object} view
             */
            removeView: function (view) {
                DatagridViewRemover.remove(view)
                    .done(function () {
                        this.getRoot().trigger('grid:view-selector:view-removed');
                    }.bind(this))
                    .fail(function (response) {
                        messenger.notify('error', response.responseJSON);
                    });
            }
        });
    }
);
