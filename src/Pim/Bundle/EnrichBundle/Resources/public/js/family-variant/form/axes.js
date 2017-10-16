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
        'pim/template/family-variant/axes'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        i18n,
        UserContext,
        fetcherRegistry,
        template
    ) {
        const sortOrdered = (first, second) => first.sort_order - second.sort_order;

        /**
         * Group attributes by attribute group
         */
        const groupAttributes = (attributes, attributeGroups) => (attributeCodes) => {
            return Object.values(attributeGroups)
                .sort(sortOrdered)
                .map(attributeGroup => {
                    return {
                        attributeGroup,
                        attributes: attributes.filter(
                            attribute =>
                                attribute.group === attributeGroup.code &&
                                attributeCodes.indexOf(attribute.code) !== -1
                        ).sort(sortOrdered)
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
            template: _.template(template),

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
                            getAttribute: getAttribute(attributes)
                        }));

                        this.$(
                            '#common-attributes-column,' +
                            '#attributes-column-level-1,' +
                            '#attributes-column-level-2'
                        )
                        .sortable({
                            connectWith: '.connected-sortable',
                            containment: this.$el,
                            tolerance: 'pointer',
                            cursor: 'move',
                            cancel: 'div.alert',
                            receive: (event, ui) => {
                                const originLevel = parseInt(ui.sender[0].dataset.level);
                                const destinationLevel = parseInt(ui.item[0].parentNode.dataset.level);
                                const movedAttributeCode = ui.item[0].dataset.attributeCode;

                                this.handleAttributeDrop(
                                    originLevel,
                                    destinationLevel,
                                    movedAttributeCode
                                );

                                this.render();
                            }
                        }).disableSelection();

                        this.$(
                            '#common-attribute-groups-column,' +
                            '#attribute-groups-column-level-1,' +
                            '#attribute-groups-column-level-2'
                        )
                        .sortable({
                            connectWith: '.connected-group-sortable',
                            containment: this.$el,
                            tolerance: 'pointer',
                            cursor: 'move',
                            cancel: 'div.alert',
                            receive: (event, ui) => {
                                const destinationLevel = parseInt(ui.item[0].parentNode.dataset.level);
                                const originLevel = parseInt(ui.sender[0].dataset.level);
                                const movedAttributes = Object.values(ui.item[0].querySelectorAll('li')).map(
                                    domElement => domElement.dataset.attributeCode
                                );

                                this.handleAttributeGroupDrop(
                                    originLevel,
                                    destinationLevel,
                                    movedAttributes
                                );

                                this.render();
                            }
                        }).disableSelection();

                        this.renderExtensions();
                    });


                return this;
            },

            handleAttributeDrop(originLevel, destinationLevel, movedAttributeCode) {
                const data = this.getFormData();
                data.variant_attribute_sets.map((attributeSet) => {
                    if (attributeSet.level === originLevel) {
                        attributeSet.attributes = attributeSet.attributes.filter(
                            attributeCode => attributeCode !== movedAttributeCode
                        );
                    }

                    if (attributeSet.level === destinationLevel) {
                        attributeSet.attributes.push(movedAttributeCode);
                    }

                    return attributeSet;
                });

                this.setData(data);
            },

            handleAttributeGroupDrop(originLevel, destinationLevel, movedAttributes) {
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
            }
        });
    }
);
