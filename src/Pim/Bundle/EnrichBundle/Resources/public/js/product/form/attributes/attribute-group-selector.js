'use strict';
/**
 * Attribute group selector extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'backbone',
        'underscore',
        'pim/form',
        'oro/mediator',
        'pim/attribute-group-manager',
        'text!pim/template/product/tab/attribute/attribute-group-selector',
        'pim/user-context',
        'pim/i18n'
    ],
    function ($, Backbone, _, BaseForm, mediator, AttributeGroupManager, template, UserContext, i18n) {
        return BaseForm.extend({
            tagName: 'ul',
            className: 'nav nav-tabs attribute-group-selector',
            template: _.template(template),
            state: null,
            badges: {},
            events: {
                'click li': 'change'
            },
            initialize: function () {
                this.state = new Backbone.Model({});
                this.listenTo(this.state, 'change', this.render);
                this.badges = {};

                this.stopListening(mediator, 'entity:action:validation_error');
                this.listenTo(mediator, 'entity:action:validation_error', this.onValidationError);

                this.stopListening(mediator, 'product:action:post_update');
                this.listenTo(mediator, 'product:action:post_update', this.onPostUpdate);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            onValidationError: function (event) {
                this.removeBadges();

                var product = event.sentData;
                var valuesErrors = event.response.values;
                if (valuesErrors) {
                    AttributeGroupManager.getAttributeGroupsForProduct(product)
                        .then(_.bind(function (attributeGroups) {
                            _.each(valuesErrors, _.bind(function (fieldError, attributeCode) {
                                var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                                    attributeGroups,
                                    attributeCode
                                );
                                this.addToBadge(attributeGroup, 'invalid');
                            }, this));

                            if (0 < valuesErrors.length) {
                                mediator.trigger('show_attribute', {attribute: _.keys(valuesErrors)[0]});
                            }
                        }, this));
                }
            },
            onPostUpdate: function () {
                this.removeBadges();
            },
            render: function () {
                this.$el.empty();
                this.$el.html(this.template({
                    current: this.state.get('current'),
                    attributeGroups: this.state.get('attributeGroups'),
                    badges: this.badges,
                    locale: UserContext.get('catalogLocale'),
                    i18n: i18n
                }));

                this.delegateEvents();

                return this;
            },
            updateAttributeGroups: function (product) {
                return AttributeGroupManager.getAttributeGroupsForProduct(product)
                    .then(_.bind(function (attributeGroups) {
                        this.state.set('attributeGroups', attributeGroups);

                        if (undefined === this.state.get('current') || !attributeGroups[this.state.get('current')]) {
                            this.state.set('current', _.first(_.keys(attributeGroups)));
                        }

                        return this.state.get('attributeGroups');
                    }, this));
            },
            change: function (event) {
                if (event.currentTarget.dataset.attributeGroup !== this.state.get('current')) {
                    this.state.set('current', event.currentTarget.dataset.attributeGroup);
                    this.trigger('attribute-group:change');
                }
            },
            getCurrent: function () {
                return this.state.get('current');
            },
            getCurrentAttributeGroup: function () {
                if (!this.state.get('attributeGroups')[this.state.get('current')]) {
                    this.state.set('current', _.first(_.keys(this.state.get('attributeGroups'))));
                }

                return this.state.get('attributeGroups')[this.state.get('current')];
            },
            setCurrent: function (current) {
                this.state.set('current', current);
            },
            getAttributeGroups: function () {
                return this.state.get('attributeGroups');
            },
            addToBadge: function (attributeGroup, code) {
                if (!this.badges[attributeGroup]) {
                    this.badges[attributeGroup] = {};
                }
                if (!this.badges[attributeGroup][code]) {
                    this.badges[attributeGroup][code] = 0;
                }

                this.badges[attributeGroup][code]++;

                this.render();
            },
            removeBadge: function (attributeGroup, code) {
                delete this.badges[attributeGroup][code];

                this.render();
            },
            removeBadges: function (code) {
                if (!code) {
                    this.badges = {};
                } else {
                    _.each(this.badges, _.bind(function (badge) {
                        delete badge[code];
                    }, this));
                }

                this.render();
            }
        });
    }
);
