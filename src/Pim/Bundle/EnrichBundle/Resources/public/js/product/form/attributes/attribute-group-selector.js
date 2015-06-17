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
        'pim/attribute-group-manager',
        'text!pim/template/product/tab/attribute/attribute-group-selector',
        'pim/user-context',
        'pim/i18n'
    ],
    function ($, Backbone, _, BaseForm, AttributeGroupManager, template, UserContext, i18n) {
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

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.on('attribute-group:remove-badges', _.bind(this.removeBadges, this));
                this.on('attribute-group:add-to-badge', _.bind(this.addToBadge, this));

                return BaseForm.prototype.configure.apply(this, arguments);
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
            addToBadge: function (event) {
                if (!this.badges[event.group]) {
                    this.badges[event.group] = {};
                }
                if (!this.badges[event.group][event.code]) {
                    this.badges[event.group][event.code] = 0;
                }

                this.badges[event.group][event.code]++;

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
