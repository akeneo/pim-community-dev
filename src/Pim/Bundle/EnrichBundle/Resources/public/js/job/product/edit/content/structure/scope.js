'use strict';
/**
 * Scope structure filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/template/export/product/edit/content/structure/scope',
        'pim/form',
        'pim/fetcher-registry',
        'pim/user-context',
        'jquery.select2',
        'pim/i18n'
    ],
    function (
        $,
        _,
        __,
        template,
        BaseForm,
        fetcherRegistry,
        UserContext,
        select2,
        i18n
    ) {
        return BaseForm.extend({
            config: {},
            className: 'AknFieldContainer',
            template: _.template(template),

            /**
             * Initializes configuration.
             *
             * @param {Object} config
             */
            initialize: function (config) {
                this.config = config.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Renders scopes dropdown.
             *
             * @return {Object}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                fetcherRegistry.getFetcher('channel').fetchAll().then(function (channels) {
                    if (!this.getScope()) {
                        this.setScope(_.first(channels).code);
                    }

                    this.$el.html(
                        this.template({
                            isEditable: this.isEditable(),
                            __: __,
                            channels: this.setChannelLabels(channels),
                            scope: this.getScope(),
                            errors: this.getParent().getValidationErrorsForField('scope')
                        })
                    );

                    this.$('.select2')
                        .select2({minimumResultsForSearch: -1})
                        .on('change', this.updateState.bind(this));

                    this.$('[data-toggle="tooltip"]').tooltip();

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Sets fallback labels for channels without a translation
             *
             * @param {Array} channels
             *
             * @return {Array}
             */
            setChannelLabels: function (channels) {
                var locale = UserContext.get('uiLocale');

                return _.map(channels, function (channel) {
                    channel.label = i18n.getLabel(channel.labels, locale, channel.code);

                    return channel;
                });
            },

            /**
             * Returns whether this filter is editable.
             *
             * @returns {boolean}
             */
            isEditable: function () {
                return undefined !== this.config.readOnly ?
                    !this.config.readOnly :
                    true;
            },

            /**
             * Sets new scope on field change.
             *
             * @param {Object} event
             */
            updateState: function (event) {
                this.setScope(event.target.value);
            },

            /**
             * Sets specified scope into root model.
             *
             * @param {String} code
             */
            setScope: function (code) {
                var data = this.getFilters();
                var before = data.structure.scope;

                data.structure.scope = code;
                this.setData(data);

                if (before !== code) {
                    this.getRoot().trigger('channel:update:after', data.structure.scope);
                }
            },

            /**
             * Gets scope from root model.
             *
             * @returns {String}
             */
            getScope: function () {
                var structure = this.getFilters().structure;

                if (_.isUndefined(structure)) {
                    return null;
                }

                return _.isUndefined(structure.scope) ? null : structure.scope;
            },

            /**
             * Get filters
             *
             * @return {object}
             */
            getFilters: function () {
                return this.getFormData().configuration.filters;
            }
        });
    }
);
