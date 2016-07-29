/**
 * Abstract attribute filter
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'pim/fetcher-registry',
    'pim/i18n',
    'pim/product-edit-form/scope-switcher',
    'pim/product-edit-form/locale-switcher'
], function (
    $,
    _,
    __,
    BaseFilter,
    FetcherRegistry,
    i18n,
    ScopeSwitcher,
    LocaleSwitcher
) {
    return BaseFilter.extend({
        /**
         * Sets the scope code on which this filter operates.
         *
         * @param {string} scope
         */
        setScope: function (scope) {
            var context = this.getFormData().context || {};
            context.scope = scope;

            this.setData({context: context});
        },

        /**
         * Gets the scope code on which this filter operates.
         *
         * @return {string}
         */
        getScope: function () {
            if (undefined === this.getFormData().context) {
                return null;
            }

            return this.getFormData().context.scope;
        },

        /**
         * Sets the locale code on which this filter operates.
         *
         * @param {string} locale
         */
        setLocale: function (locale) {
            var context = this.getFormData().context || {};
            context.locale = locale;

            this.setData({context: context});
        },

        /**
         * Gets the locale code on which this filter operates.
         *
         * @return {string}
         */
        getLocale: function () {
            if (undefined === this.getFormData().context) {
                return null;
            }

            return this.getFormData().context.locale;
        },

        /**
         * {@inheritdoc}
         */
        renderElements: function () {
            FetcherRegistry.getFetcher('attribute')
                .fetch(this.getField())
                .then(function (attribute) {
                    if (this.isEditable()) {
                        this.addContextDropdowns(attribute);
                    } else {
                        this.addContextLabels(attribute);
                    }
                }.bind(this))
                .then(function () {
                    BaseFilter.prototype.renderElements.apply(this, arguments);
                }.bind(this));
        },

        /**
         * Adds the context dropdown to the filter in edit mode according to attribute information.
         *
         * @param {Object} attribute
         */
        addContextDropdowns: function (attribute) {
            if (attribute.scopable) {
                var scopeSwitcher = new ScopeSwitcher();

                this.listenTo(
                    scopeSwitcher,
                    'pim_enrich:form:scope_switcher:pre_render',
                    function (scopeEvent) {
                        if (this.getScope()) {
                            scopeEvent.scopeCode = this.getScope();
                        }
                    }
                );

                this.listenTo(
                    scopeSwitcher,
                    'pim_enrich:form:scope_switcher:change',
                    function (scopeEvent) {
                        this.setScope(scopeEvent.scopeCode);
                    }
                );

                this.addElement(
                    'after-input',
                    'scope-switcher',
                    scopeSwitcher.render().$el
                );
            }

            if (attribute.localizable) {
                var localeSwitcher = new LocaleSwitcher();

                this.listenTo(
                    localeSwitcher,
                    'pim_enrich:form:locale_switcher:pre_render',
                    function (localeEvent) {
                        if (this.getLocale()) {
                            localeEvent.localeCode = this.getLocale();
                        }
                    }
                );

                this.listenTo(
                    localeSwitcher,
                    'pim_enrich:form:locale_switcher:change',
                    function (localeEvent) {
                        this.setLocale(localeEvent.localeCode);
                    }
                );

                this.addElement(
                    'after-input',
                    'locale-switcher',
                    localeSwitcher.render().$el
                );
            }
        },

        /**
         * Adds the context labels to the filter in view mode according to attribute information.
         *
         * @param {Object} attribute
         */
        addContextLabels: function (attribute) {
            var promises = [];

            if (attribute.scopable && this.getScope()) {
                promises.push(FetcherRegistry.getFetcher('channel')
                    .fetch(this.getScope())
                    .then(function (channel) {
                        return $('<span>').html(channel.label);
                    })
                );
            }

            if (attribute.localizable && this.getLocale()) {
                promises.push(
                    $.Deferred()
                        .resolve($('<span>').html(i18n.getFlag(this.getLocale())))
                        .promise()
                );
            }

            $.when.apply($, promises)
                .then(function () {
                    var container = $('<span class="filter-context">');
                    _.each(_.toArray(arguments), function (item) {
                        container.append(item);
                    });

                    this.addElement(
                        'after-input',
                        'filter-context',
                        container
                    );
                }.bind(this));
        }
    });
});
