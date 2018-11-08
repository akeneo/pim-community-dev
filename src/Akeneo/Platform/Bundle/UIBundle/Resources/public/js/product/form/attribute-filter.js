'use strict';
/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/form/tab/attribute/attribute-filter'
    ],
    function (
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'AknDropdown AknButtonList-item nav nav-tabs attribute-filter',
            template: _.template(template),

            events: {
                'click .AknDropdown-menuLink': 'onChange'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:switch_values_filter', this.setCurrent.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const currentFilter = this.getCurrentFilter();

                this.$el.html(this.template({
                    filters: this.getFilters()
                        .filter(filter => 'function' !== typeof(filter.isVisible) || filter.isVisible())
                        .map((filter) => ({ code: filter.getCode(), label: filter.getLabel()})),
                    currentFilter: { code: currentFilter.getCode(), label: currentFilter.getLabel() },
                    __: __
                }));

                this.delegateEvents();
            },

            /**
             * Facade method delegating the values filtering to the current filter
             *
             * @param {Object} values
             *
             * @returns {Promise}
             */
            filterValues(values) {
                return this.getCurrentFilter().filterValues(values);
            },

            /**
             * Returns all filters extensions registered as children
             *
             * @returns {Array}
             */
            getFilters() {
                return Object.values(this.extensions);
            },

            /**
             * Return the current filter
             *
             * @returns {Object}
             */
            getCurrentFilter() {
                const currentFilterCode = sessionStorage.getItem('current_attribute_filter');
                const filter = this.getFilters().find((filter) => currentFilterCode === filter.getCode());

                if (undefined === filter || !filter.isVisible()) {
                    return this.getFilters()[0];
                }

                return filter;
            },

            /**
             * Sets the new current filter and triggers an event.
             *
             * @param {Event} event
             */
            onChange(event) {
                this.setCurrent(event.currentTarget.dataset.code);
            },

            /**
             * Set the current filter
             *
             * @param {string} filterCode
             */
            setCurrent(filterCode) {
                if (filterCode === sessionStorage.getItem('current_attribute_filter')) {
                    return;
                }

                sessionStorage.setItem('current_attribute_filter', filterCode);
                this.trigger('attribute_filter:change');
                this.$el.removeClass('open');
            }
        });
    }
);
