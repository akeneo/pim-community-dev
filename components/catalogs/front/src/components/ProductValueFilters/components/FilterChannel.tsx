import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {MultiSelectInput} from 'akeneo-design-system';
import {ProductValueFiltersValues} from '../models/ProductValueFiltersValues';
import {useChannelsByCodes} from '../../../hooks/useChannelsByCodes';
import {Channel} from '../../../models/Channel';
import {useInfiniteChannels} from '../../../hooks/useInfiniteChannels';
import {useUniqueEntitiesByCode} from '../../../hooks/useUniqueEntitiesByCode';

type Props = {
    productValueFilters: ProductValueFiltersValues;
    onChange: (values: ProductValueFiltersValues) => void;
    isInvalid: boolean;
};

export const FilterChannel: FC<Props> = ({productValueFilters, onChange, isInvalid}) => {
    const translate = useTranslate();

    const {data: selected} = useChannelsByCodes(productValueFilters?.channels ?? []);
    const {data: results, fetchNextPage} = useInfiniteChannels();
    const channels = useUniqueEntitiesByCode<Channel>(selected, results);

    return (
        <>
            <MultiSelectInput
                value={productValueFilters?.channels ?? []}
                emptyResultLabel={translate('akeneo_catalogs.product_value_filters.filters.channel.no_matches')}
                openLabel={translate('akeneo_catalogs.product_value_filters.action.open')}
                removeLabel={translate('akeneo_catalogs.product_value_filters.action.remove')}
                placeholder={translate('akeneo_catalogs.product_value_filters.filters.channel.placeholder')}
                onChange={v => onChange({...productValueFilters, channels: v})}
                onNextPage={fetchNextPage}
                invalid={isInvalid}
                data-testid='product-value-filter-by-channel'
            >
                {channels?.map(option => (
                    <MultiSelectInput.Option key={option.code} title={option.label} value={option.code}>
                        {option.label}
                    </MultiSelectInput.Option>
                ))}
            </MultiSelectInput>
        </>
    );
};
