'use strict';

/**
 * Family attributes settings table view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'jquery',
        'pim/form',
        'pim/template/family/tab/attributes/attributes',
        'pim/user-context',
        'pim/security-context',
        'pim/i18n',
        'pim/fetcher-registry',
        'pim/dialog',
        'oro/messenger',
        'oro/loading-mask'
    ],
    function (
        _,
        __,
        $,
        BaseForm,
        template,
        UserContext,
        SecurityContext,
        i18n,
        FetcherRegistry,
        Dialog,
        Messenger,
        LoadingMask
    ) {
        return BaseForm.extend({
            className: 'tabsection-content tab-content',
            attributeRequiredIconClass: 'AknAcl-icon AknAcl-icon--granted icon-ok required',
            attributeNotRequiredIconClass: 'AknAcl-icon icon-circle non-required',
            collapsedClass: 'AknGrid-bodyContainer--collapsed',
            requiredLabel: __('pim_enrich.form.family.tab.attributes.required_label'),
            notRequiredLabel: __('pim_enrich.form.family.tab.attributes.not_required_label'),
            identifierAttributeType: 'pim_catalog_identifier',
            template: _.template(template),
            errors: [],
            catalogLocale: UserContext.get('catalogLocale'),
            channels: null,
            events: {
                'click .group': 'toggleGroup',
                'click .attribute-requirement i': 'toggleAttribute',
                'click .remove-attribute': 'onRemoveAttribute'
            },
            readOnly: false,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_fetch',
                    this.render
                );
                this.listenTo(this.getRoot(), 'pim_enrich:form:update_read_only', (readOnly) => {
                    this.readOnly = readOnly;

                    this.render();
                });

                this.listenTo(
                    this.getRoot(),
                    'add-attribute:add',
                    this.onAddAttributes
                );

                this.listenTo(
                    this.getRoot(),
                    'add-attribute-group:add',
                    this.onAddAttributesByAttributeGroups
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                var data = this.getFormData();
                var attributeGroupsToFetch = _.unique(_.pluck(data.attributes, 'group'));

                $.when(
                    FetcherRegistry.getFetcher('channel').fetchAll(),
                    FetcherRegistry.getFetcher('attribute-group').fetchByIdentifiers(
                        attributeGroupsToFetch,
                        {
                            'full_attributes': false,
                            'apply_filters': false
                        }
                    )
                ).then((channels, attributeGroups) => {
                    this.channels = channels;
                    var groupedAttributes = _.groupBy(data.attributes, 'group');

                    _.sortBy(groupedAttributes, (attributes, group) => {
                        return _.findWhere(attributeGroups, {code: group}).sort_order;
                    });

                    _.each(groupedAttributes, function (attributes, group) {
                        attributes = _.sortBy(attributes, function (attribute) {
                            return attribute.sort_order;
                        });

                        groupedAttributes[group] = attributes;
                    });

                    this.$el.html(this.template({
                        label: __(this.config.label),
                        requiredLabel: this.requiredLabel,
                        notRequiredLabel: this.notRequiredLabel,
                        groupedAttributes: groupedAttributes,
                        attributeRequirements: data.attribute_requirements,
                        channels: this.channels,
                        attributeGroups: _.map(attributeGroups, function (group) {
                            var panel = $('tbody[data-group="' + group.code + '"]');
                            group.collapsed = $(panel).hasClass(this.collapsedClass);

                            return group;
                        }.bind(this)),
                        colspan: (this.channels.length + 2),
                        i18n: i18n,
                        identifierAttributeType: this.identifierAttributeType,
                        catalogLocale: this.catalogLocale,
                        readOnly: this.readOnly
                    }));

                    $(this.$el).find('[data-original-title]').tooltip();

                    this.delegateEvents();
                    this.renderExtensions();
                });
            },

            /**
             * Toggle expand/collapse attribute group accordion
             *
             * @param {Object} event
             */
            toggleGroup: function (event) {
                var target = event.currentTarget;
                $(target).find('i').toggleClass('icon-expand-alt icon-collapse-alt');
                $(target).parent().toggleClass(this.collapsedClass);

                return this;
            },

            /**
             * Toggle attribute requirement
             *
             * @param {Object} event
             */
            toggleAttribute: function (event) {
                var target = event.currentTarget;

                if (!SecurityContext.isGranted('pim_enrich_family_edit_attributes')) {
                    return this;
                }

                if (!this.isAttributeEditable(target.dataset.type)) {
                    return this;
                }

                if (this.readOnly) {
                    return this;
                }

                var attribute = target.dataset.attribute;
                var channel = target.dataset.channel;

                if ('true' === target.dataset.required) {
                    this.removeFromAttributeRequirements(attribute, channel);
                } else {
                    this.addToAttributeRequirements(attribute, channel);
                }

                return this.render();
            },

            /**
             * Checks if attribute is editable
             *
             * @param {string} type
             *
             * @returns {boolean}
             */
            isAttributeEditable: function (type) {
                return this.identifierAttributeType !== type;
            },

            /**
             * Adds attribute to channel requirements
             *
             * @param {string} attribute
             * @param {string} channel
             */
            addToAttributeRequirements: function (attribute, channel) {
                var data = this.getFormData();
                var requirements = data.attribute_requirements[channel] || [];
                requirements.push(attribute);
                data.attribute_requirements[channel] = requirements;

                return this.setData(data);
            },

            /**
             * Removes attribute from channels requirements
             *
             * @param {string} attribute
             * @param {string} channel
             */
            removeFromAttributeRequirements: function (attribute, channel) {
                var data = this.getFormData();
                data.attribute_requirements[channel] = data.attribute_requirements[channel] ?
                    data.attribute_requirements[channel].filter(function (item) {
                        return attribute !== item;
                    }) : [];
                this.setData(data);
            },

            /**
             * Removes attribute from family upon user confirmation
             *
             * Checks if user has rights to remove attributes
             * Checks if attribute is not used as label
             * Checks if attribute is not used as image
             * Checks if attribute is not used as axis in a family variant
             *
             * @param {Object} event
             */
            onRemoveAttribute: function (event) {
                event.preventDefault();
                const attributeAsLabel = this.getFormData().attribute_as_label;
                const attributeAsImage = this.getFormData().attribute_as_image;
                const attributesUsedAsAxis = this.getFormData().meta.attributes_used_as_axis;

                if (!SecurityContext.isGranted('pim_enrich_family_edit_attributes')) {
                    return false;
                }

                const attributeToRemove = event.currentTarget.dataset.attribute;

                if (attributeAsLabel === attributeToRemove) {
                    Messenger.notify(
                        'error',
                        __('pim_enrich.entity.family.info.cant_remove_attribute_as_label')
                    );

                    return false;

                } else if (attributeAsImage === attributeToRemove) {
                    Messenger.notify(
                        'error',
                        __('pim_enrich.entity.family.info.cant_remove_attribute_as_image')
                    );

                    return false;
                } else if (_.contains(attributesUsedAsAxis, attributeToRemove)) {
                    Messenger.notify(
                        'error',
                        __('pim_enrich.entity.family.info.cant_remove_attribute_used_as_axis')
                    );

                    return false;
                }

                this.removeAttribute(attributeToRemove);
            },

            /**
             * Adds selected attributes to family
             *
             * @param {Object} event
             */
            onAddAttributes: function (event) {
                var options = {
                    options: {
                        identifiers: event.codes,
                        limit: event.codes.length
                    }
                };
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                $.when(
                    FetcherRegistry.getFetcher('attribute')
                        .search(options)
                ).then((attributes) => {
                    _.each(attributes, (attribute) => {
                        this.addAttribute(attribute);
                    });

                    this.render();
                }).always(function () {
                    loadingMask.hide().$el.remove();
                });
            },

            /**
             * Adds attributes associated with selected groups
             *
             * @param {Object} event
             */
            onAddAttributesByAttributeGroups: function (event) {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                $.when(
                    FetcherRegistry.getFetcher('attribute-group')
                        .search({
                            options: {
                                identifiers: event.codes,
                                limit: event.codes.length
                            }
                        }),
                    FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
                ).then((attributeGroups, identifier) => {
                    var existingAttributes = _.pluck(this.getFormData().attributes, 'code');
                    var groupsAttributes = [].concat.apply(
                        [],
                        _.pluck(attributeGroups, 'attributes')
                    );
                    var attributesToAdd = _.filter(groupsAttributes, function (attribute) {
                        return !_.contains(existingAttributes, attribute) &&
                            attribute !== identifier.code;
                    });

                    return FetcherRegistry.getFetcher('attribute')
                        .search({
                            options: {
                                identifiers: attributesToAdd,
                                limit: attributesToAdd.length
                            }
                        });
                }).then((attributes) => {
                    _.each(attributes, (attribute) => {
                        this.addAttribute(attribute);
                    });

                    this.render();
                }).always(function () {
                    loadingMask.hide().$el.remove();
                });
            },

            /**
             * Removes attribute from family
             *
             * @return {Object}
             */
            removeAttribute: function (attribute) {
                _.each(this.channels, function (channel) {
                    this.removeFromAttributeRequirements(attribute, channel.code);
                }.bind(this));

                var data = this.getFormData();

                data.attributes.splice(
                    _.pluck(data.attributes, 'code').indexOf(attribute),
                    1
                );

                this.setData(data);

                return this.render();
            },

            /**
             * Adds attribute to family
             *
             * @param {Object} attribute
             */
            addAttribute: function (attribute) {
                var data = this.getFormData();
                if ('undefined' !== typeof _.findWhere(
                    data.attributes, {
                        code: attribute.code
                    })) {
                    return this;
                }

                data.attributes.push(attribute);
                this.setData(data);
            }
        });
    }
);
