'use strict';
/**
 * Copy extension override able to copy from product working copy
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/product-edit-form/attributes/copy',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        mediator,
        Copy,
        FetcherRegistry
    ) {
        return Copy.extend({
            sources: ['working_copy', 'draft'],
            currentSource: '',
            workingCopy: {},

            /**
             * @inheritdoc
             */
            configure: function () {
                this.currentSource = this.sources[0];
                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);
                this.listenTo(mediator, 'draft:action:show_working_copy', this.showWorkingCopy);

                this.onExtensions('source_switcher:render:before', this.onSourceSwitcherRender);
                this.onExtensions('source_switcher:source_change', this.onSourceChange);

                return Copy.prototype.configure.apply(this, arguments);
            },

            /**
            * Event callback called just after product is fetched form backend
            *
            * @param {Object} event
            */
            onProductPostFetch: function (event) {
                this.workingCopy = event.originalProduct;
            },

            /**
             * Keep any source switcher uo-to-date for its rendering
             *
             * @param {Object} context
             */
            onSourceSwitcherRender: function (context) {
                context.sources       = this.sources;
                context.currentSource = this.currentSource;
            },

            /**
             * Update the current source and re-render the extension
             *
             * @param {string} newSource
             *
             * @throws {Error} If specified source code is invalid
             */
            onSourceChange: function (newSource) {
                if (-1 === this.sources.indexOf(newSource)) {
                    throw new Error('Invalid source code "' + newSource + '"');
                }

                this.currentSource = newSource;
                this.triggerContextChange();
            },

            /**
             * Return the current product id
             *
             * @returns {number}
             */
            getProductId: function () {
                return this.getFormData().meta.id;
            },

            /**
             * Retrieve the current draft using the draft fetcher
             *
             * @returns {Promise}
             */
            getDraft: function () {
                return FetcherRegistry.getFetcher('product-draft')
                    .fetchForProduct(this.getProductId());
            },

            /**
            * @inheritdoc
             *
             * @throws {Error} If current source is not set or not valid
            */
            getSourceData: function () {
                var data = {};
                switch (this.currentSource) {
                    case 'working_copy':
                        data = this.workingCopy.values;
                        break;
                    case 'draft':
                        data = this.getFormData().values;
                        break;
                    default:
                        throw new Error("No valid source is currently selected to copy from");
                }

                return data;
            },

            /**
            * @inheritdoc
            */
            canBeCopied: function (field) {
                return $.when(
                        this.getDraft(),
                        Copy.prototype.canBeCopied.apply(this, arguments)
                    ).then(_.bind(function (draft, canBeCopied) {
                        return draft.isValueChanged(field, this.locale, this.scope) || canBeCopied;
                    }, this));
            },

            /**
             * Set the current source to "working copy" and enter in copy mode
             */
            showWorkingCopy: function () {
                this.currentSource = 'working_copy';
                this.startCopying();
            }
        });
    }
);
