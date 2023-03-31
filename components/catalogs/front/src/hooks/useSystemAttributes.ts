import {useMemo} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Attribute} from '../models/Attribute';
import {Target} from '../components/ProductMapping/models/Target';

type QueryParams = {
    target: Target | null;
    search: string | null;
};

export const useSystemAttributes = ({target, search}: QueryParams): Attribute[] => {
    const translate = useTranslate();

    let targetTypeKey = 'default';
    if (target !== null) {
        targetTypeKey = target.type;

        if (null !== target.format && '' !== target.format) {
            targetTypeKey += `+${target.format}`;
        }
    }

    const systemAttributeType = {
        'array<string>': ['categories', 'family'],
        boolean: ['status'],
        string: ['categories', 'family'],
        default: ['categories', 'family', 'status'],
    };

    const systemAttributes = [
        {
            code: 'categories',
            label: translate('akeneo_catalogs.product_mapping.source.system_attributes.categories.label'),
            type: 'categories',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'family',
            label: translate('akeneo_catalogs.product_mapping.source.system_attributes.family.label'),
            type: 'family',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
        {
            code: 'status',
            label: translate('akeneo_catalogs.product_mapping.source.system_attributes.status.label'),
            type: 'status',
            scopable: false,
            localizable: false,
            attribute_group_code: 'system',
            attribute_group_label: 'System',
        },
    ];

    const filterAttributes = useMemo(() => {
        if (!(targetTypeKey in systemAttributeType)) {
            return [];
        }
        let attributes = systemAttributes.filter(attribute =>
            systemAttributeType[targetTypeKey as keyof typeof systemAttributeType].includes(attribute.type)
        );
        if (null !== search) {
            const regex = new RegExp(search, 'i');
            attributes = attributes.filter(attribute => attribute.label.match(regex));
        }
        return attributes;
    }, [targetTypeKey, search, systemAttributeType, systemAttributes]);

    return filterAttributes;
};
