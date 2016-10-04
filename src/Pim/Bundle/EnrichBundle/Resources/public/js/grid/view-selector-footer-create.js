'use strict';

/**
 * Footer extension for the Datagrid View Selector.
 *
 * Contains a "create" button to allow the user to create a view from the current
 * state of the grid (regarding filters and columns).
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
        'text!pim/template/grid/view-selector/footer/create',
        'pim/dialog',
        'routing',
        'pim/datagrid/state',
        'pim/saver/datagrid-view',
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
        DatagridState,
        DatagridViewSaver,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click [data-action="prompt-creation"]': 'promptCreation'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    buttonTitle: __('grid.view_selector.create')
                }));

                return this;
            },

            /**
             * Prompt the view creation modal.
             */
            promptCreation: function () {
                this.getRoot().trigger('grid:view-selector:close-selector');

                var placeholder = __('grid.view_selector.placeholder');
                var content = '<input name="new-view-label" type="text" placeholder="' + placeholder + '">';
                var label = null;

                Dialog.confirm(content, __('grid.view_selector.choose_label'), this.saveView.bind(this));

                var $input = $('input[name="new-view-label"]');
                var $submitBtn = $input.parents('.modal').find('.ok').hide();

                $input.on('input', function () {
                    label = $input.val();
                    if (!label.length) {
                        $submitBtn.hide();
                    } else {
                        $submitBtn.show();
                    }
                }).on('keypress', function (e) {
                    if (13 === (e.keyCode || e.which) && label.length) {
                        $submitBtn.trigger('click');
                    }
                });
            },

            /**
             * Save the current Datagrid view in database and triggers an event to the parent
             * to select it.
             */
            saveView: function () {
                var gridState = DatagridState.get(this.getRoot().gridAlias, ['filters', 'columns']);
                var newView = {
                    filters: gridState.filters,
                    columns: gridState.columns,
                    label: $('input[name="new-view-label"]').val()
                };

                DatagridViewSaver.save(newView, this.getRoot().gridAlias)
                    .done(function (response) {
                        this.getRoot().trigger('grid:view-selector:view-created', response.id);
                    }.bind(this))
                    .fail(function (response) {
                        _.each(response.responseJSON, function (error) {
                            messenger.notificationFlashMessage('error', error);
                        });
                    });
            }
        });
    }
);
