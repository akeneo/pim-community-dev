'use strict';
/**
 * Locale structure filter
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
        'pim/template/export/product/edit/content/structure/locales',
        'pim/form',
        'pim/fetcher-registry',
        'jquery.select2'
    ],
    function (
        $,
        _,
        __,
        template,
        BaseForm,
        fetcherRegistry
    ) {
        return BaseForm.extend({
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
             * Configures this extension.
             *
             * @return {Promise}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'channel:update:after', this.channelUpdated.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Renders locales dropdown.
             *
             * @returns {Object}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                fetcherRegistry.getFetcher('channel')
                    .fetch(this.getFilters().structure.scope)
                    .always(function (scope) {
                        this.$el.html(
                            this.template({
                                isEditable: this.isEditable(),
                                __: __,
                                locales: this.getLocales(),
                                availableLocales: !scope ? [] : scope.locales,
                                errors: this.getParent().getValidationErrorsForField('locales')
                            })
                        );

                        this.$('.select2').select2().on('change', this.updateState.bind(this));
                        this.$('[data-toggle="tooltip"]').tooltip();

                        this.renderExtensions();
                    }.bind(this));

                return this;
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
             * Sets new locales on field change.
             *
             * @param {Object} event
             */
            updateState: function (event) {
                this.setLocales(event.val);
            },

            /**
             * Sets specified locales into root model.
             *
             * @param {Array} codes
             */
            setLocales: function (codes) {
                var data = this.getFilters();
                var before = data.structure.locales;

                data.structure.locales = codes;
                this.setData(data);

                if (before !== codes) {
                    this.getRoot().trigger('locales:update:after', codes);
                }
            },

            /**
             * Gets locales from root model.
             *
             * @returns {Array}
             */
            getLocales: function () {
                var structure = this.getFilters().structure;

                if (_.isUndefined(structure)) {
                    return [];
                }

                return _.isUndefined(structure.locales) ? [] : structure.locales;
            },

            /**
             * Resets locales after channel has been modified then re-renders the view.
             */
            channelUpdated: function () {
                this.initializeDefaultLocales()
                    .then(function () {
                        this.render();
                    }.bind(this));
            },

            /**
             * Sets locales corresponding to the current scope (default state).
             *
             * @return {Promise}
             */
            initializeDefaultLocales: function () {
                return fetcherRegistry.getFetcher('channel')
                    .fetch(this.getCurrentScope())
                    .then(function (scope) {
                        this.setLocales(_.pluck(scope.locales, 'code'));
                    }.bind(this));
            },

            /**
             * Gets current scope from root model.
             *
             * @return {String}
             */
            getCurrentScope: function () {
                return this.getFilters().structure.scope;
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
