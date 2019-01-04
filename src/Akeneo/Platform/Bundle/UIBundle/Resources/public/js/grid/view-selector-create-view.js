'use strict';

/**
 * Create extension for the Datagrid View Selector.
 * It displays a button near the selector to allow the user to create a new view.
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
        'backbone',
        'pim/form',
        'pim/template/grid/view-selector/create-view',
        'pim/template/form/creation/modal',
        'pim/template/grid/view-selector/create-view-label-input',
        'pim/datagrid/state',
        'pim/saver/datagrid-view',
        'oro/messenger'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        templateModalContent,
        templateInput,
        DatagridState,
        DatagridViewSaver,
        messenger
    ) {
        return BaseForm.extend({
            template: _.template(template),
            templateModalContent: _.template(templateModalContent),
            templateInput: _.template(templateInput),
            tagName: 'span',
            className: 'create-button',
            events: {
                'click .create': 'promptCreateView'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if ('view' !== this.getRoot().currentViewType) {
                    this.$el.html('');

                    return this;
                }

                this.$el.html(this.template({
                    label: __('pim_datagrid.view_selector.create_view')
                }));

                this.$('[data-toggle="tooltip"]').tooltip();

                return this;
            },

            /**
             * Prompt the view creation modal.
             */
            promptCreateView: function () {
                this.getRoot().trigger('grid:view-selector:close-selector');

                let modal = new Backbone.BootstrapModal({
                    subtitle: __('pim_datagrid.view_selector.view'),
                    title: __('pim_common.create'),
                    picture: 'illustrations/Views.svg',
                    okText: __('pim_common.save'),
                    okCloses: false,
                    content: this.templateModalContent({
                        fields: this.templateInput({
                            placeholder: __('pim_datagrid.view_selector.placeholder'),
                            label: __('pim_datagrid.view_selector.choose_label')
                        })
                    }),
                });

                modal.open();

                const $submitButton = modal.$el.find('.ok').addClass('AknButton--disabled');

                modal.on('ok', this.saveView.bind(this, modal));
                modal.on('cancel', function () {
                    modal.remove();
                }.bind(this));
                modal.$('input[name="new-view-label"]').on('input', function (event) {
                    var label = event.target.value;

                    if (!label.length) {
                        $submitButton.addClass('AknButton--disabled');
                    } else {
                        $submitButton.removeClass('AknButton--disabled');
                    }
                });
                modal.$('input[name="new-view-label"]').on('keypress', function (event) {
                    if (13 === (event.keyCode || event.which) && event.target.value.length) {
                        $submitButton.trigger('click');
                    }
                });
            },

            /**
             * Save the current Datagrid view in database and triggers an event to the parent
             * to select it.
             *
             * @param {object} modal
             */
            saveView: function (modal) {
                if ($('.modal .ok').hasClass('AknButton--disabled')) {
                    return;
                }

                var gridState = DatagridState.get(this.getRoot().gridAlias, ['filters', 'columns']);
                var newView = {
                    filters: gridState.filters,
                    columns: gridState.columns,
                    label: modal.$('input[name="new-view-label"]').val()
                };

                DatagridViewSaver.save(newView, this.getRoot().gridAlias)
                    .done(function (response) {
                        this.getRoot().trigger('grid:view-selector:view-created', response.id);
                        modal.close();
                        modal.remove();
                    }.bind(this))
                    .fail(function (response) {
                        _.each(response.responseJSON, function (error) {
                            messenger.notify('error', error);
                        });
                    });
            }
        });
    }
);
