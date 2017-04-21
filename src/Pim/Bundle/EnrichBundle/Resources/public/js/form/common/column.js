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
        'text!pim/template/form/column'
    ],
    function (_, __, BaseForm, template) {
        return BaseForm.extend({
            className: 'AknColumn',

            template: _.template(template),

            events: {
                'click .AknColumn-collapseButton': '_toggleColumn'
            },

            config: {},

            /**
             * Configuration:
             * - collapsed: This is a key to identify each module using column, to store if the column is collapsed
             *   or not. If you want to use the different collapsed states, use different "collapsed" value.
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
                if (this._isCollapsed()) {
                    this._toggleColumn();
                }

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            _toggleColumn: function (e) {
                $(this.$el).toggleClass('AknColumn--collapsed');
                this._setCollapsed($(this.$el).hasClass('AknColumn--collapsed'));
            },

            /**
             * Returns true if the column is collapsed.
             * It uses the session storage with a key attached to this module.
             * If no key was found, returns false by default.
             *
             * @returns {boolean}
             */
            _isCollapsed: function () {
                var result = sessionStorage.getItem(this._getSessionStorageKey());
                if (null === result) {
                    this._setCollapsed(false);

                    return false;
                }

                return '1' === result;
            },

            /**
             * Stores in the session storage if the column is collapsed or not.
             *
             * @param value
             */
            _setCollapsed: function (value) {
                sessionStorage.setItem(this._getSessionStorageKey(), value ? '1' : '0');
            },

            /**
             * Returns the key used by the session storage for this module.
             *
             * @returns {string}
             */
            _getSessionStorageKey: function () {
                return 'collapsedColumn_' + this.config.collapsed;
            }
        });
    }
);
