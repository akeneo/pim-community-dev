/**
 * Adds behavior to allow a div to be resized
 * Stores and restores the resized width
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['jquery'], function($) {
    return {
        /**
         * @property {Number} maxWidth The maximum width of the panel in pixels
         * @property {Number} minWidth The minimum width of the panel in pixels
         * @property {String|HTMLElement} container A selector or element that will be resizable
         * @property {String} storageKey The name of the localStorage key to store the width
         */
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
                throw new Error('You must specify the container as an element or CSS selector');
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
            const container = $(this.options.container);
            const resizableInstance = container.resizable('instance');

            if (undefined !== resizableInstance) {
                container.resizable('destroy');
            }
        },

        /**
         * Store the last resized width of the element in localStorage
         * @param  {jQuery.Event} event The jQuery event when the resize dragging stops
         * @param  {Object} ui The data for the resizable element
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
});
