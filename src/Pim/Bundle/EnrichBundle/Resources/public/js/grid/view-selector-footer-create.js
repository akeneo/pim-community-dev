'use strict';

/**
 * Footer extension for the Datagrid View Selector.
 *
 * Contains a "create" button to allow the user to create a view from the current
 * state of the grid (regarding filters and columns).
 *
 * @author    Willy MESNAGE <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/grid/view-selector/footer/create'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click .create-button': 'triggerClick',
                'click .create-action': 'triggerClick'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var hasMultipleExtensions = this.hasMultipleExtensions();

                this.$el.html(this.template({
                    isMultiple: hasMultipleExtensions,
                    createButtonTitle: __('grid.view_selector.create')
                }));

                if (hasMultipleExtensions) {
                    this.$('[data-toggle="dropdown"]').dropdown();
                }

                this.renderExtensions(hasMultipleExtensions);

                return this;
            },

            /**
             * Method called on a click on a create action in this footer create module.
             * This method triggers an event to extensions with the extension code who should handle it.
             *
             * @param {Event} event
             */
            triggerClick: function (event) {
                var extensionCode = event.currentTarget.getAttribute('data-extension-code');

                if (null === extensionCode) {
                    extensionCode = _.findWhere(this.extensions, {targetZone: 'action-list'}).code;
                }

                this.triggerExtensions('grid:view-selector:trigger-create', {extensionCode: extensionCode});
            },

            /**
             * Return whether this module has several extensions in its 'action-list' dropzone.
             *
             * @returns {boolean}
             */
            hasMultipleExtensions: function () {
                return _.where(this.extensions, {targetZone: 'action-list'}).length > 1;
            },

            /**
             * {@inheritdoc}
             */
            renderExtensions: function (hasMultipleExtensions) {
                this.initializeDropZones();

                _.each(this.extensions, function (extension) {
                    if ('action-list' === extension.targetZone && hasMultipleExtensions) {
                        this.renderExtensionAsList(extension);
                    } else {
                        this.renderExtension(extension);
                    }
                }.bind(this));

                return this;
            },

            /**
             * Render a single extension as list
             *
             * @param {Object} extension
             */
            renderExtensionAsList: function (extension) {
                this.getZone(extension.targetZone).appendChild(
                    $('<li class="create-action AknDropdown-menuLink" data-extension-code="'+extension.code+'">')
                        .append(extension.el)[0]
                );

                extension.render();
            }
        });
    }
);
