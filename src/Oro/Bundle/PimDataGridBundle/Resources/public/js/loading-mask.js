'use strict';

/**
 * Loading mask widget
 *
 * @export  oro/loading-mask
 * @class   oro.LoadingMask
 * @extends Backbone.View
 */

/* global define */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator'
    ], function(
        $,
        _,
        Backbone,
        __
    ) {

    return Backbone.View.extend({
        /** @property {Boolean} */
        displayed: false,

        /** @property {String} */
        className: 'AknLoadingMask loading-mask',

        /**
         * Show loading mask
         *
         * @return {*}
         */
        show: function() {
            this.$el.show();
            this.displayed = true;

            return this;
        },

        /**
         * Hide loading mask
         *
         * @return {*}
         */
        hide: function() {
            this.$el.hide();
            this.displayed = false;

            return this;
        },

        /**
         * Render loading mask
         *
         * @return {*}
         */
        render: function() {
            this.hide();

            return this;
        }
    });
});
