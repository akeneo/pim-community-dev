'use strict';
/**
 * Import switcher extension.
 * This will display all the main actions related to import (upload, import now)
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'pim/template/import/switcher'
    ],
    function (
        _,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'AknButtonList',
            template: _.template(template),
            actions: [],
            events: {
                'click .switcher-action': 'switch'
            },
            currentActionCode: null,

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.actions = [];

                this.listenTo(this.getRoot(), 'switcher:register', this.registerAction);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (_.isEmpty(this.actions)) {
                    return;
                }

                if (null === this.currentActionCode) {
                    this.setCurrentActionCode(_.first(this.actions).code);
                }

                this.$el.empty().append(this.template({
                    actions: this.actions,
                    current: this.currentActionCode
                }));

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Registers a new main action
             *
             * @param {Object} action
             * @param {String} action.label The label to display in this switcher
             * @param {String} action.code  The extension code to display on click
             */
            registerAction: function (action) {
                this.actions.push(action);
                this.render();
            },

            /**
             * Switches a new action to display
             *
             * @param {Event} event
             */
            switch: function (event) {
                this.setCurrentActionCode(event.target.dataset.code);
                this.render();
            },

            /**
             * Sets the new displayed action
             *
             * @param {String} code The code of the current extension
             */
            setCurrentActionCode: function (code) {
                this.currentActionCode = code;
                this.getRoot().trigger('switcher:switch', { code: code });
            }
        });
    }
);
