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
        'pim/product-edit-form/attributes/copy'
    ],
    function (
        $,
        _,
        mediator,
        Copy
    ) {
        return Copy.extend({
            sources: ['working_copy', 'draft'],
            currentSource: '',

            /**
             * @inheritdoc
             */
            configure: function () {
                this.currentSource = _.first(this.sources);
                this.listenTo(mediator, 'pim_enrich:form:draft:show_working_copy', this.showWorkingCopy);

                this.onExtensions('source_switcher:render:before', this.onSourceSwitcherRender);
                this.onExtensions('source_switcher:source_change', this.onSourceChange);

                return Copy.prototype.configure.apply(this, arguments);
            },

            /**
             * Keep any source switcher uo-to-date for its rendering
             *
             * @param {Object} context
             */
            onSourceSwitcherRender: function (context) {
                context.sources       = this.getSources();
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
                if (!_.contains(this.getSources(), newSource)) {
                    throw new Error('Invalid source code "' + newSource + '"');
                }

                this.currentSource = newSource;
                this.triggerContextChange();
            },

            /**
             * Return the current working copy
             *
             * @returns {Object|null}
             */
            getWorkingCopy: function () {
                var workingCopy = this.getFormData().meta.working_copy;

                return _.isEmpty(workingCopy) ? null : workingCopy;
            },

            /**
             * Return the sources list optionally filtered
             * If there is no working copy it means that the user owns the product, so draft is not a valid source
             *
             * @returns {Array}
             */
            getSources: function () {
                if (null === this.getWorkingCopy()) {
                    return _.without(this.sources, 'draft');
                } else {
                    return this.sources;
                }
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
                        data = null === this.getWorkingCopy() ?
                            this.getFormData().values :
                            this.getWorkingCopy().values;
                        break;
                    case 'draft':
                        data = this.getFormData().values;
                        break;
                    default:
                        throw new Error('No valid source is currently selected to copy from');
                }

                return data;
            },

            /**
            * @inheritdoc
            */
            canBeCopied: function (field) {
                var params = {
                    field: field,
                    canBeCopied: Copy.prototype.canBeCopied.apply(this, arguments),
                    locale: this.locale,
                    scope: this.scope
                };

                mediator.trigger('pim_enrich:form:field:can_be_copied', params);

                return params.canBeCopied;
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
