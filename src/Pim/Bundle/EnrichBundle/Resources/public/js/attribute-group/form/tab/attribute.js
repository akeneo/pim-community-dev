'use strict';

/**
 * Attribute selection tab
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/security-context',
        'pim/dialog',
        'text!pim/template/form/attribute-group/tab/attribute',
        'jquery-ui'
    ],
    function (
        _,
        __,
        BaseForm,
        i18n,
        UserContext,
        FetcherRegistry,
        SecurityContext,
        Dialog,
        template
    ) {
        return BaseForm.extend({
            className: 'AknTabContainer-content tabbable tabs-left',
            template: _.template(template),
            otherAttributes: [],

            events: {
                'click .remove-attribute': 'removeAttributeRequest'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.config.tabCode ? this.config.tabCode : this.code,
                    label: __(this.config.title)
                });

                this.onExtensions('add-attribute:add', this.addAttributes.bind(this));

                return FetcherRegistry.getFetcher('attribute').search({attribute_groups: 'other'}).then(function (attributes) {
                    this.otherAttributes = _.pluck(attributes, 'code');
                }.bind(this)).then(function () {
                    return BaseForm.prototype.configure.apply(this, arguments);
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('attribute')
                    .fetchByIdentifiers(this.getFormData().attributes, {rights: 0})
                    .then(function (attributes) {
                        var attributes = _.map(attributes, function (attribute) {
                            var sortOrder = this.getFormData().attributes_sort_order[attribute.code] ?
                                this.getFormData().attributes_sort_order[attribute.code] :
                                _.keys(this.getFormData().attributes_sort_order) + 1;
                            return _.extend(
                                {},
                                attribute,
                                {sort_order: this.getFormData().attributes_sort_order[attribute.code]}
                            );
                        }.bind(this));
                        var attributes = _.sortBy(attributes, 'sort_order');

                        this.$el.html(this.template({
                            attributes: attributes,
                            i18n: i18n,
                            UserContext: UserContext,
                            __: __
                        }));

                        this.$('tbody').sortable({
                            handle: '.handle',
                            containment: 'parent',
                            tolerance: 'pointer',
                            update: this.updateAttributeOrders.bind(this),
                            helper: function(e, tr) {
                                var $originals = tr.children();
                                var $helper = tr.clone();
                                $helper.children().each(function(index) {
                                    $(this).width($originals.eq(index).width());
                                });

                                return $helper;
                            }
                        });

                        BaseForm.prototype.render.apply(this, arguments);
                    }.bind(this));
            },

            /**
             * Update the sort order of attributes
             */
            updateAttributeOrders: function () {
                var sortOrder = _.reduce(this.$('tbody > tr'), function (previous, current, order) {
                    var next = _.extend({}, previous);
                    next[current.dataset.attributeCode] = order;

                    return next;
                }, {});
                var channel = _.extend({}, this.getFormData());
                channel['attributes_sort_order'] = sortOrder;

                this.setData(channel);

                this.render();
            },

            /**
             * Add attributes to the model
             *
             * @param {Event}
             */
            addAttributes: function (event) {
                var channel = _.extend({}, this.getFormData());
                channel['attributes'] = _.union(channel['attributes'], event.codes);
                this.otherAttributes = _.difference(this.otherAttributes, event.codes);

                this.setData(channel);

                this.render();
            },

            /**
             * Add attributes to the model
             *
             * @param {Event}
             */
            removeAttributeRequest: function (event) {
                if (!SecurityContext.isGranted(this.config.removeAttributeACL)) {
                    return;
                }

                var code = event.currentTarget.dataset.attributeCode;

                Dialog.confirm(
                    __(this.config.confirmation.message, {attribute: code}),
                    __(this.config.confirmation.title),
                    function () {
                        this.removeAttribute(code);
                    }.bind(this)
                );
            },

            removeAttribute: function (code) {
                var channel = _.extend({}, this.getFormData());
                channel['attributes'] = _.without(channel['attributes'], code);
                delete channel['attributes_sort_order'][code];
                this.otherAttributes = _.union(this.otherAttributes, [code]);

                this.setData(channel);

                this.render();
            },

            getOtherAttributes: function () {
                return this.otherAttributes;
            }
        });
    });
