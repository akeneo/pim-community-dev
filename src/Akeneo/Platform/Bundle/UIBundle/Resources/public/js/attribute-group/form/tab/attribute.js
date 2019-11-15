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
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/security-context',
        'pim/dialog',
        'pim/template/form/attribute-group/tab/attribute',
        'jquery-ui'
    ],
    function (
        $,
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
            className: 'tabbable tabs-left',
            template: _.template(template),
            otherAttributes: [],
            attributeSortOrderKey: 'attributes_sort_order',

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

                return FetcherRegistry.getFetcher('attribute')
                    .search({attribute_groups: 'other', rights: 0})
                    .then(function (attributes) {
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
                        attributes = attributes.map((attribute) => {
                            let sortOrder = this.getFormData().attributes_sort_order[attribute.code];

                            //This updates the sort order if the attribute is new on the collection
                            if (undefined === sortOrder) {
                                sortOrder = Object.keys(this.getFormData().attributes_sort_order).length + 1;
                            }

                            return Object.assign({}, attribute, { sort_order: sortOrder });
                        });

                        attributes.sort((first, second) => first.sort_order - second.sort_order);

                        this.$el.empty().append(this.template({
                            attributes: attributes,
                            i18n: i18n,
                            UserContext: UserContext,
                            __: __,
                            hasRightToRemove: this.hasRightToRemove(),
                            canSortAttributes: SecurityContext.isGranted(this.config.sortAttributesACL)
                        }));

                        this.$('.attribute-list').sortable({
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

                        this.delegateEvents();

                        BaseForm.prototype.render.apply(this, arguments);
                    }.bind(this));

                return this;
            },

            /**
             * Update the sort order of attributes
             */
            updateAttributeOrders: function () {
                var sortOrder = _.reduce(this.$('.attribute'), function (previous, current, order) {
                    var next = _.extend({}, previous);
                    next[current.dataset.attributeCode] = order;

                    return next;
                }, {});
                var attributeGroup = _.extend({}, this.getFormData());
                attributeGroup.attributes_sort_order = sortOrder;

                this.setData(attributeGroup);

                this.render();
            },

            /**
             * Add attributes to the model
             *
             * @param {Event}
             */
            addAttributes: function (event) {
                var attributeGroup = _.extend({}, this.getFormData());
                attributeGroup.attributes = _.union(attributeGroup.attributes, event.codes);
                this.otherAttributes = _.difference(this.otherAttributes, event.codes);

                this.setData(attributeGroup);

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

                Dialog.confirmDelete(
                    __(this.config.confirmation.message, {attribute: code}),
                    __(this.config.confirmation.title),
                    function () {
                        this.removeAttribute(code);
                    }.bind(this),
                    __(this.config.confirmation.subTitle),
                    __(this.config.confirmation.buttonText)
                );
            },

            /**
             * Remove attribute from collection
             *
             * @param {string} code
             */
            removeAttribute: function (code) {
                var attributeGroup = _.extend({}, this.getFormData());
                attributeGroup.attributes = _.without(attributeGroup.attributes, code);
                delete attributeGroup.attributes_sort_order[code];
                this.otherAttributes = _.union(this.otherAttributes, [code]);

                this.setData(attributeGroup);

                this.render();
            },

            /**
             * Get the list of attributes that are in the 'other' group
             *
             * @return {array}
             */
            getOtherAttributes: function () {
                return this.otherAttributes;
            },

            /**
             * Does the user has right to remove an attribute
             *
             * @return {Boolean}
             */
            hasRightToRemove: function () {
                var currentAttributeGroupIsNotOther = this.config.otherGroup !== this.getFormData().code;

                return currentAttributeGroupIsNotOther &&
                    SecurityContext.isGranted(this.config.removeAttributeACL)
            },

            /**
             * Does the user has right to add an attribute
             *
             * @return {Boolean}
             */
            hasRightToAdd: function () {
                var currentAttributeGroupIsNotOther = this.config.otherGroup !== this.getFormData().code;

                return currentAttributeGroupIsNotOther &&
                    SecurityContext.isGranted(this.config.addAttributeACL)
            }
        });
    });
