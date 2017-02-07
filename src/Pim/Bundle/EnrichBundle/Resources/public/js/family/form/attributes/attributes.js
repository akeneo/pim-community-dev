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
        'text!pim/template/family/tab/attributes/attributes',
        'pim/user-context',
        'pim/i18n',
        'pim/fetcher-registry',
        'pim/dialog',
        'oro/loading-mask'
    ],
    function (
        _,
        __,
        $,
        BaseForm,
        template,
        UserContext,
        i18n,
        FetcherRegistry,
        Dialog,
        LoadingMask
    ) {
        return BaseForm.extend({
            className: 'tabsection-content tab-content',
            attributeRequiredIconClass: 'AknAcl-icon AknAcl-icon--granted icon-ok required',
            attributeNotRequiredIconClass: 'AknAcl-icon icon-circle non-required',
            requiredLabel: __('pim_enrich.form.family.tab.attributes.required_label'),
            notRequiredLabel: __('pim_enrich.form.family.tab.attributes.not_required_label'),
            deleteDialogTitle: __('pim_enrich.form.family.tab.attributes.dialog.delete_title'),
            deleteDialogMessage: __('pim_enrich.form.family.tab.attributes.dialog.delete_message'),
            identifierAttribute: 'pim_catalog_identifier',
            template: _.template(template),
            errors: [],
            catalogLocale: UserContext.get('catalogLocale'),
            channels: null,
            attributeGroups: null,
            attributeToRemove: null,
            events: {
                'click tr.group': 'toggleGroup',
                'click .attribute-requirement i': 'toggleAttribute',
                'click a.remove-attribute': 'onRemoveAttribute'
            },

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

                if (!this.channels || !this.attributeGroups) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                    $.when(
                        FetcherRegistry.getFetcher('channel').fetchAll(),
                        FetcherRegistry.getFetcher('attribute-group').fetchAll()
                    ).then(function (channels, attributeGroups) {
                        this.channels = channels;
                        this.attributeGroups = attributeGroups;

                        return this.render();
                    }.bind(this)).always(function () {
                        loadingMask.hide().$el.remove();
                    });

                    return;
                }

                var data = this.getFormData();
                var groupedAttributes = _.groupBy(data.attributes, function (attribute) {
                    return attribute.group_code;
                });

                _.sortBy(groupedAttributes, function (attributes, group) {
                    return this.attributeGroups[group].sort_order;
                }.bind(this));

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
                    attributeGroups: this.attributeGroups,
                    colspan: (this.channels.length + 2),
                    i18n: i18n,
                    identifierAttribute: this.identifierAttribute,
                    catalogLocale: this.catalogLocale
                }));

                $(this.$el).find('[data-original-title]').tooltip();

                this.delegateEvents();
                this.renderExtensions();
            },

            /**
             * Toggle expand/collapse attribute group accordion
             *
             * @param {Object} event
             */
            toggleGroup: function (event) {
                var target = event.currentTarget;
                $(target).parent().find('tr:not(.group)').toggle();
                $(target).find('i').toggleClass('icon-expand-alt icon-collapse-alt');

                return this;
            },

            /**
             * Toggle attribute requirement
             *
             * @param {Object} event
             */
            toggleAttribute: function (event) {
                var target = event.currentTarget;

                if (!this.isAttributeEditable(target.dataset.type)) {
                    return this;
                }

                var attribute = target.dataset.attribute;
                var channel = target.dataset.channel;

                if ('true' === target.dataset.required) {
                    this.removeFromAttributeRequirements(attribute, channel);
                    $(target).attr('class', this.attributeNotRequiredIconClass);
                    target.dataset.originalTitle = this.notRequiredLabel;
                    target.dataset.required = 'false';
                    $(target).tooltip('show');

                    return this;
                }

                this.addToAttributeRequirements(attribute, channel);
                $(target).attr('class', this.attributeRequiredIconClass);
                target.dataset.originalTitle = this.requiredLabel;
                target.dataset.required = 'true';
                $(target).tooltip('show');

                return this;
            },

            /**
             * Checks if attribute is editable
             *
             * @param {string} type
             *
             * @returns {boolean}
             */
            isAttributeEditable: function (type) {
                return this.identifierAttribute !== type;
            },

            /**
             * Adds attribute to channel requirements
             *
             * @param {string} attribute
             * @param {string} channel
             */
            addToAttributeRequirements: function (attribute, channel) {
                var data = this.getFormData();
                data.attribute_requirements[channel].push(attribute);
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
                data.attribute_requirements[channel] = data
                    .attribute_requirements[channel]
                    .filter(function (item) {
                        return attribute !== item;
                    });
                this.setData(data);
            },

            /**
             * Removes attribute from family
             *
             * @param {Object} event
             */
            onRemoveAttribute: function (event) {
                this.attributeToRemove = event.currentTarget.dataset.attribute;
                var attr = _.findWhere(
                    this.getFormData().attributes,
                    {code: this.attributeToRemove}
                );
                var name = i18n.getLabel(attr.labels, this.catalogLocale, attr.code);

                Dialog.confirm(
                    this.deleteDialogTitle,
                    this.deleteDialogMessage
                        .replace('%name%', name),
                    this.removeAttribute.bind(this)
                );
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
                ).then(function (attributes) {
                    _.each(attributes, function (attribute) {
                        this.addAttribute(attribute);
                    }.bind(this));

                    this.render();
                }.bind(this)).always(function () {
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
                        })
                ).then(function (attributeGroups) {
                    var existingAttributes = _.pluck(this.getFormData().attributes, 'code');
                    var groupsAttributes = [].concat.apply([], _.pluck(attributeGroups, 'attributes'));
                    var attributesToAdd = _.filter(groupsAttributes, function (attribute) {
                        return !_.contains(existingAttributes, attribute);
                    });

                    return FetcherRegistry.getFetcher('attribute')
                        .search({
                            options: {
                                identifiers: attributesToAdd,
                                limit: attributesToAdd.length
                            }
                        });
                }.bind(this)).then(function (attributes) {
                    _.each(attributes, function (attribute) {
                        this.addAttribute(attribute);
                    }.bind(this));

                    this.render();
                }.bind(this)).always(function () {
                    loadingMask.hide().$el.remove();
                });
            },

            /**
             * Removes attribute from family
             *
             * @return {Object}
             */
            removeAttribute: function () {
                var attribute = this.attributeToRemove;
                this.attributeToRemove = null;

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
