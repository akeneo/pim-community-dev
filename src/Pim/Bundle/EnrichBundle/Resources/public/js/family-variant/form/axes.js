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
                            .reduce((result, attributeSet) =>
                                [...result, ...attributeSet.attributes],
                                []
                            );
                        const attributeCodes = [
                            ...axesAttributeCodes,
                            ...family.attributes.map(attribute => attribute.code)
                        ];

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
                            .filter(attributeCode => axesAttributeCodes.indexOf(attributeCode) === -1)

                        this.$el.empty().append(this.template({
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

                        this.renderExtensions();
                    });


                return this;
            }
        });
    }
);
