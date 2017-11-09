'use strict';

/**
 * Family variant axes form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/i18n',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/template/family-variant/axes',
        'pim/template/family-variant/attribute-group'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        i18n,
        UserContext,
        fetcherRegistry,
        template,
        attributeGroupTemplate
    ) {
        const sortOrdered = (first, second) => first.sort_order - second.sort_order;

        /**
         * Group attributes by attribute group
         */
        const groupAttributes = (attributes, attributeGroups) => (attributeCodes, lockedAttributes) => {
            return Object.values(attributeGroups)
                .sort(sortOrdered)
                .map(attributeGroup => {
                    const groupAttributes = attributes.filter(
                        attribute =>
                            attribute.group === attributeGroup.code &&
                            attributeCodes.indexOf(attribute.code) !== -1
                    ).sort(sortOrdered);

                    const locked = groupAttributes.filter(
                        attribute => !lockedAttributes.includes(attribute.code)
                    ).length === 0;

                    return {
                        attributeGroup: Object.assign({}, attributeGroup, {locked}),
                        attributes: groupAttributes
                    };
                })
                .filter(section => section.attributes.length !== 0);
        };

        /**
         * Get attribute from attribute code
         */
        const getAttribute = attributes => attributeCode =>
            attributes.find(attribute => attribute.code === attributeCode);

        return BaseForm.extend({
            className: 'family-variant-levels AknFamilyVariant',
            events: {
                'click .delete-attribute': 'removeAttributeFromVariantAttributeSet',
                'click .delete-attribute-group': 'removeAttributeGroupFromVariantAttributeSet'
            },
            template: _.template(template),
            attributeGroupTemplate: _.template(attributeGroupTemplate),

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                const familyVariant = this.getFormData();
                fetcherRegistry.getFetcher('family')
                    .fetch(familyVariant.family)
                    .then((family) => {
                        const axesAttributeCodes = familyVariant.variant_attribute_sets
                            .reduce(
                                (result, attributeSet) => result.concat(attributeSet.attributes),
                                []
                            );
                        const attributeCodes = axesAttributeCodes.concat(
                            family.attributes.map(attribute => attribute.code)
                        );

                        return $.when(
                            fetcherRegistry.getFetcher('attribute-group').fetchAll(),
                            fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(attributeCodes),
                            axesAttributeCodes,
                            family
                        );
                    })
                    .then((attributeGroups, attributes, axesAttributeCodes, family) => {
                        const commonAttributes = family.attributes
                            .map(attribute => attribute.code)
                            .filter(attributeCode => axesAttributeCodes.indexOf(attributeCode) === -1);

                        const axisAttributes = familyVariant
                            .variant_attribute_sets
                            .map(set => set.axes)
                            .reduce((allAxes, axes) => allAxes.concat(axes));

                        const lockedAttributes = family.attributes
                            .filter(attribute => {
                                const isUnique = attribute.unique;
                                const isAxis = axisAttributes.includes(attribute.code);

                                return isAxis || isUnique;
                            })
                            .map(attribute => attribute.code);

                        this.$el.empty().append(this.template({
                            lockedAttributes,
                            axisAttributes,
                            familyVariant,
                            attributeGroups,
                            family,
                            commonAttributes,
                            UserContext,
                            i18n,
                            __,
                            groupAttributes: groupAttributes(attributes, attributeGroups),
                            getAttribute: getAttribute(attributes),
                            renderSection: (level, attributes) => {
                                return this.attributeGroupTemplate({lockedAttributes, level, attributes, i18n, UserContext, __, axisAttributes});
                            }
                        }));

                        this.$(
                            '#attributes-column-level-0,' +
                            '#attributes-column-level-1,' +
                            '#attributes-column-level-2'
                        )
                        .sortable({
                            connectWith: '.connected-sortable',
                            containment: this.$el,
                            tolerance: 'pointer',
                            cursor: 'move',
                            cancel: 'div.alert',
                            items: '.movable',
                            receive: this.moveAttribute(lockedAttributes)
                        }).disableSelection();

                        this.$(
                            '#attribute-groups-column-level-0,' +
                            '#attribute-groups-column-level-1,' +
                            '#attribute-groups-column-level-2'
                        )
                        .sortable({
                            connectWith: '.connected-group-sortable',
                            containment: this.$el,
                            tolerance: 'pointer',
                            cursor: 'move',
                            cancel: 'div.alert',
                            items: '.movable-group',
                            receive: this.moveAttributes(lockedAttributes)
                        }).disableSelection();

                        this.renderExtensions();
                    });


                return this;
            },

            moveAttribute(lockedAttributes) {
                return (event, ui) => {
                    const originLevel = parseInt(ui.sender[0].dataset.level);
                    const destinationLevel = parseInt(ui.item[0].parentNode.dataset.level);
                    const movedAttributes = [ui.item[0].dataset.attributeCode];

                    this.handleAttributesDrop(
                        originLevel,
                        destinationLevel,
                        movedAttributes
                    );
                }
            },

            moveAttributes(lockedAttributes) {
                return (event, ui) => {
                    const destinationLevel = parseInt(ui.item[0].parentNode.dataset.level);
                    const originLevel = parseInt(ui.sender[0].dataset.level);
                    const movedAttributes = Object.values(ui.item[0].querySelectorAll('li')).map(
                        domElement => domElement.dataset.attributeCode
                    ).filter(movedAttribute => !lockedAttributes.includes(movedAttribute));

                    this.handleAttributesDrop(
                        originLevel,
                        destinationLevel,
                        movedAttributes
                    );
                }
            },

            removeAttributeFromVariantAttributeSet(event) {
                const $attributeToRemove = $(event.currentTarget.parentElement);
                const variantAttributeSetLevel = $attributeToRemove.closest('[data-level]').data('level');
                const removedAttributes = [$attributeToRemove.data('attribute-code')];

                this.handleAttributesRemoval(variantAttributeSetLevel, removedAttributes);
            },

            removeAttributeGroupFromVariantAttributeSet(event) {
                const $attributeGroupToRemove = $(event.currentTarget).parents('.attribute-group-section');
                const variantAttributeSetLevel = $attributeGroupToRemove.parent().data('level');
                const removedAttributes = $attributeGroupToRemove.find('.attribute.movable').toArray()
                    .map(element => element.dataset.attributeCode);

                this.handleAttributesRemoval(variantAttributeSetLevel, removedAttributes);
            },

            handleAttributesDrop(originLevel, destinationLevel, movedAttributes) {
                if (originLevel >= destinationLevel) {
                    this.render();

                    return;
                }

                const data = this.getFormData();
                data.variant_attribute_sets.map((attributeSet) => {
                    if (attributeSet.level === originLevel) {
                        attributeSet.attributes = attributeSet.attributes.filter(
                            attributeCode => movedAttributes.indexOf(attributeCode) === -1
                        );
                    }

                    if (attributeSet.level === destinationLevel) {
                        attributeSet.attributes.push(...movedAttributes);
                    }

                    return attributeSet;
                });

                this.setData(data);

                this.render();
            },

            handleAttributesRemoval(level, removedAttributes) {
                var data = this.getFormData();
                data.variant_attribute_sets.map((attributeSet) => {
                    if (attributeSet.level === level) {
                        attributeSet.attributes = attributeSet.attributes.filter(item => -1 === removedAttributes.indexOf(item));
                    }
                });

                this.setData(data);
                this.render();
            }
        });
    }
);
