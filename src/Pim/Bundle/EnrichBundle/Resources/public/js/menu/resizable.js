/**
 * Adds behavior to allow a div to be resized
 * Stores and restores the resized width
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['jquery'], function($) {
    const Resizable = {
        options: {
            maxWidth: null,
            minWidth: null,
            container: null,
            storageKey: null
        },

        /**
         * Set the div with the provided container as resizable
         * with jQuery UI.
         *
         * @param {Object} options
         */
        set(options = {}) {
            this.options = Object.assign(this.options, options);

            const { maxWidth, minWidth, container } = this.options;

            if (null === container) {
                throw new Error('You must specify the container');
            }

            $(container).resizable({
                maxWidth,
                minWidth,
                handles: 'e',
                create: this.restoreWidth.bind(this),
                stop: this.storeWidth.bind(this)
            });
        },

        /**
         * Destroy the resizable and handler events
         */
        destroy() {
            $(this.options.container).resizable('destroy');
        },

        /**
         * Store the last resized width of the element in localStorage
         * @param  {jQuery.Event} event The jQuery event when the resize dragging stops
         * @param  {Object} ui The object containing the data for the div
         */
        storeWidth(event, ui) {
            const { minWidth, storageKey } = this.options;
            const containerWidth = ui.size.width || minWidth;
            localStorage.setItem(`resizable-${storageKey}`, containerWidth);
        },

        /**
         * Get the stored width from localStorage and set it on the div.
         * If there is no previously stored width, use the minWidth
         */
        restoreWidth() {
            const { storageKey, container, minWidth } = this.options;
            const width = localStorage.getItem(`resizable-${storageKey}`);
            $(container).outerWidth(width || minWidth);
        }
    };

    return Resizable;
});
