'use strict';
/**
 * Display a vertical column for navigation or filters
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/form/column'
    ],
    function (_, __, BaseForm, template) {
        return BaseForm.extend({
            className: 'AknColumn',

            template: _.template(template),

            events: {
                'click .AknColumn-collapseButton': 'toggleColumn'
            },

            config: {},

            /**
             * Configuration:
             * - stateCode: This is a key to identify each module using column, to store if the column is collapsed
             *   or not. If you want to use the different collapsed states, use different "stateCode" value.
             *
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());
                if (this.isCollapsed()) {
                    this.setCollapsed(true);
                }

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            toggleColumn: function () {
                this.setCollapsed(!this.isCollapsed());
            },

            /**
             * Returns true if the column is collapsed.
             * It uses the session storage with a key attached to this module.
             * If no key was found, returns false by default.
             *
             * @returns {boolean}
             */
            isCollapsed: function () {
                var result = sessionStorage.getItem(this.getSessionStorageKey());

                if (null === result) {
                    return false;
                }

                return '1' === result;
            },

            /**
             * Stores in the session storage if the column is collapsed or not.
             *
             * @param {boolean} value
             */
            setCollapsed: function (value) {
                sessionStorage.setItem(this.getSessionStorageKey(), value ? '1' : '0');

                if (value) {
                    this.$el.addClass('AknColumn--collapsed');
                } else {
                    this.$el.removeClass('AknColumn--collapsed');
                }
            },

            /**
             * Returns the key used by the session storage for this module.
             *
             * @returns {string}
             */
            getSessionStorageKey: function () {
                return 'collapsedColumn_' + this.config.stateCode;
            }
        });
    }
);
