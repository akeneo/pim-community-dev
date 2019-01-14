'use strict';
/**
 * Group selector extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/user-context',
        'pim/form',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/template/form/group-selector'
    ],
    function ($, _, __, UserContext, BaseForm, mediator, fetcherRegistry, template) {
        return BaseForm.extend({
            tagName: 'ul',
            className: 'AknVerticalNavtab nav nav-tabs group-selector',
            all: {},
            template: _.template(template),
            elements: [],
            badges: {},
            events: {
                'click li': 'change'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.badges   = {};
                this.elements = [];

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                return BaseForm.prototype.configure.apply(this, arguments).then(() => {
                    return fetcherRegistry.getFetcher('locale').fetchActivated().then((locales) => {
                        this.all = {
                            code: 'all_attribute_groups',
                            labels: {},
                            sort_order: -1
                        };

                        locales.forEach((locale) => {
                            this.all.labels[locale.code] = __(
                                'pim_common.all'
                            );
                        });
                    });
                });
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty();
                this.$el.html(this.template({
                    current: this.getCurrent(),
                    elements: this.getElements(),
                    badges: this.badges
                }));

                this.delegateEvents();

                return this;
            },

            /**
             * Set the element collection
             *
             * @param {Array} elements
             */
            setElements: function (elements) {
                this.elements = elements;
                this.elements[this.all.code] = this.all;
                this.ensureDefault();
            },

            /**
             * On attribute group change
             *
             * @param {Event} event
             */
            change: function (event) {
                this.setCurrent(event.currentTarget.dataset.element);
            },

            /**
             * Get current attribute group
             *
             * @return {String}
             */
            getCurrent: function () {
                return sessionStorage.getItem('current_select_group_' + this.code);
            },

            /**
             * Set current attribute group
             *
             * @param {String} current
             * @param {Object} options
             */
            setCurrent: function (current, options) {
                options = options || {silent: false};

                if (current !== this.getCurrent()) {
                    sessionStorage.setItem('current_select_group_' + this.code, current);

                    if (!options.silent) {
                        this.trigger('group:change');
                    }

                    this.render();
                }
            },

            /**
             * Ensure default values for the current attribute group
             */
            ensureDefault: function () {
                if (_.isUndefined(this.getCurrent()) ||
                    !this.getElements()[this.getCurrent()]
                ) {
                    if (!this.elements[this.all.code]) {
                        this.elements[this.all.code] = this.all;
                    }
                    this.setCurrent(this.all.code, {silent: true});
                }
            },

            /**
             * Get the current attribute group
             *
             * @return {String}
             */
            getCurrentElement: function () {
                return this.getElements()[this.getCurrent()];
            },

            /**
             * Get all attribute groups
             *
             * @return {object}
             */
            getElements: function () {
                return this.elements;
            },

            /**
             * Increment count on attribute group for the given code
             *
             * @param {String} element
             * @param {String} code
             */
            addToBadge: function (element, code) {
                if (!this.badges[element]) {
                    this.badges[element] = {};
                }
                if (!this.badges[element][code]) {
                    this.badges[element][code] = 0;
                }

                this.badges[element][code]++;

                this.render();
            },

            /**
             * Remove badges for all attribute groups
             *
             * @param {String} code
             */
            removeBadges: function (code) {
                if (!code) {
                    this.badges = {};
                } else {
                    _.each(this.badges, function (badge) {
                        delete badge[code];
                    }.bind(this));
                }

                this.render();
            },

            isAll: function () {
                return this.getCurrent() === this.all.code;
            }
        });
    }
);
