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
        'oro/loading-mask',
        'oro/mediator'
    ],
    (
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
        LoadingMask,
        mediator
    ) => {
        return BaseForm.extend({
            className: 'tabsection-content tab-content',
            attributeRequiredIconClass: 'AknAcl-icon AknAcl-icon--granted icon-ok required',
            attributeNotRequiredIconClass: 'AknAcl-icon icon-circle non-required',
            collapsedClass: 'AknGrid-bodyContainer--collapsed',
            requiredLabel: __('pim_enrich.entity.family.module.attributes.required_label'),
            notRequiredLabel: __('pim_enrich.entity.family.module.attributes.not_required_label'),
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
            initialize(config) {
                this.config = config.config;
            },

            /**
             * {@inheritdoc}
             */
            configure() {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_fetch',
                    this.render
                );
                this.listenTo(this.getRoot(), 'pim_enrich:form:update_read_only', (readOnly) => {
                    this.readOnly = readOnly;

                    this.render();
                });

                this.listenTo(mediator, 'mass-edit:form:lock', () => {
                    this.readOnly = true;
                });

                this.listenTo(mediator, 'mass-edit:form:unlock', () => {
                    this.readOnly = false;
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
            render() {
                if (!this.configured) {
                    return this;
                }

                this.getTemplateContext().then((context) => {
                    this.$el.html(this.template(context));

                    $(this.$el).find('[data-original-title]').tooltip();

                    this.delegateEvents();
                    this.renderExtensions();
                });
            },

            getTemplateContext() {
                const data = this.getFormData();
                const attributeGroupsToFetch = _.unique(_.pluck(data.attributes, 'group'));

                return $.when(
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

                    const sortedAttributes = _.sortBy(data.attributes, (attribute) => {
                       return _.findWhere(attributeGroups, {code: attribute.group}).sort_order;
                    });

                    const groupedAttributes = _.groupBy(sortedAttributes, 'group');

                    _.each(groupedAttributes, (attributes, group) => {
                        attributes = _.sortBy(attributes, (attribute) => attribute.sort_order);

                        groupedAttributes[group] = attributes;
                    });

                    return {
                        label: __(this.config.label),
                        groupedAttributes: groupedAttributes,
                        channels: this.channels,
                        attributeGroups: _.map(attributeGroups, (group) => {
                            const panel = $('tbody[data-group="' + group.code + '"]');
                            group.collapsed = $(panel).hasClass(this.collapsedClass);

                            return group;
                        }),
                        colspan: (this.channels.length + 1),
                        i18n: i18n,
                        identifierAttributeType: this.identifierAttributeType,
                        catalogLocale: this.catalogLocale,
                        readOnly: this.readOnly,
                        isAttributeRequirementRequired: this.isAttributeRequirementRequired.bind(this),
                        isAttributeEditable: this.isAttributeEditable.bind(this),
                        getAttributeRequirementTooltip: this.getAttributeRequirementTooltip.bind(this)
                    };
                });
            },

            /**
             * @param {Object} attribute
             * @param {Object} channel
             *
             * @returns {boolean}
             */
            isAttributeRequirementRequired(attribute, channel) {
                const attributeRequirements = this.getFormData().attribute_requirements;

                if (undefined === attributeRequirements[channel.code]) {
                    return false;
                }

                return -1 < attributeRequirements[channel.code].indexOf(attribute.code);
            },

            /**
             * @param {Object} attribute
             * @param {Object} channel
             *
             * @returns {string}
             */
            getAttributeRequirementTooltip(attribute, channel) {
                return this.isAttributeRequirementRequired(attribute, channel)
                    ? this.requiredLabel
                    : this.notRequiredLabel;
            },

            /**
             * Toggle expand/collapse attribute group accordion
             *
             * @param {Object} event
             */
            toggleGroup(event) {
                const target = event.currentTarget;
                $(target).find('div').toggleClass('AknGrid-expand--expanded');
                $(target).parent().toggleClass(this.collapsedClass);

                return this;
            },

            /**
             * Toggle attribute requirement
             *
             * @param {Object} event
             */
            toggleAttribute(event) {
                const attributeCode = event.currentTarget.dataset.attribute;
                const attributeType = event.currentTarget.dataset.type;
                const channelCode = event.currentTarget.dataset.channel;

                if (!SecurityContext.isGranted('pim_enrich_family_edit_attributes')) {
                    return;
                }

                if (!this.isAttributeEditable(channelCode, attributeCode, attributeType)) {
                    return;
                }

                if ('true' === event.currentTarget.dataset.required) {
                    this.removeFromAttributeRequirements(attributeCode, channelCode);
                } else {
                    this.addToAttributeRequirements(attributeCode, channelCode);
                }

                this.render();
            },

            /**
             * Checks if attribute is editable
             *
             * @param {string} channelCode
             * @param {string} attributeCode
             * @param {string} attributeType
             *
             * @returns {boolean}
             */
            isAttributeEditable(channelCode, attributeCode, attributeType) {
                return !this.readOnly && this.identifierAttributeType !== attributeType;
            },

            /**
             * Adds attribute to channel requirements
             *
             * @param {string} attribute
             * @param {string} channel
             */
            addToAttributeRequirements(attribute, channel) {
                const data = this.getFormData();
                const requirements = data.attribute_requirements[channel] || [];
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
            removeFromAttributeRequirements(attribute, channel) {
                const data = this.getFormData();
                data.attribute_requirements[channel] = data.attribute_requirements[channel] ?
                    data.attribute_requirements[channel].filter((item) => attribute !== item) : [];
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
            onRemoveAttribute(event) {
                if (this.readOnly) {
                    return;
                }

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
                        __('pim_enrich.entity.family.flash.update.cant_remove_attribute_as_label')
                    );

                    return false;

                } else if (attributeAsImage === attributeToRemove) {
                    Messenger.notify(
                        'error',
                        __('pim_enrich.entity.family.flash.update.cant_remove_attribute_as_image')
                    );

                    return false;
                } else if (_.contains(attributesUsedAsAxis, attributeToRemove)) {
                    Messenger.notify(
                        'error',
                        __('pim_enrich.entity.family.flash.update.cant_remove_attribute_used_as_axis')
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
            onAddAttributes(event) {
                const options = {
                    options: {
                        identifiers: event.codes,
                        limit: event.codes.length
                    }
                };
                const loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                $.when(
                    FetcherRegistry.getFetcher('attribute')
                        .search(options)
                ).then((attributes) => {
                    _.each(attributes, (attribute) => {
                        this.addAttribute(attribute);
                    });

                    this.render();
                }).always(() => {
                    loadingMask.hide().$el.remove();
                });
            },

            /**
             * Adds attributes associated with selected groups
             *
             * @param {Object} event
             */
            onAddAttributesByAttributeGroups(event) {
                const loadingMask = new LoadingMask();
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
                    const existingAttributes = _.pluck(this.getFormData().attributes, 'code');
                    const groupsAttributes = [].concat.apply(
                        [],
                        _.pluck(attributeGroups, 'attributes')
                    );
                    const attributesToAdd = _.filter(groupsAttributes, (attribute) => {
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
                }).always(() => {
                    loadingMask.hide().$el.remove();
                });
            },

            /**
             * Removes attribute from family
             *
             * @return {Object}
             */
            removeAttribute(attribute) {
                _.each(this.channels, (channel) => {
                    this.removeFromAttributeRequirements(attribute, channel.code);
                });

                const data = this.getFormData();

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
            addAttribute(attribute) {
                const data = this.getFormData();
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
