import {useMemo} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Attribute} from '../../../models/Attribute';

export const useSystemAttributesFactories = (): Attribute[] => {
    const translate = useTranslate();
    return useMemo(
        () => [
            {
                code: 'categories',
                label: translate('akeneo_catalogs.product_mapping.source.system_attributes.categories.label'),
                type: 'categories',
                scopable: false,
                localizable: true,
                attribute_group_code: 'system',
                attribute_group_label: 'System',
            },
        ],
        [translate]
    );
};
