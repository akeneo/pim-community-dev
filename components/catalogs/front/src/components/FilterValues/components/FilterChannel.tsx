import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {List, MultiSelectInput} from 'akeneo-design-system';
import {FilterValuesValues} from '../models/FilterValuesValues';
import {useChannelsByCodes} from '../../ProductSelection/hooks/useChannelsByCodes';
import {useInfiniteChannels} from '../../ProductSelection/hooks/useInfiniteChannels';
import {useUniqueEntitiesByCode} from '../../ProductSelection/hooks/useUniqueEntitiesByCode';
import {Channel} from '../models/Channel';

type Props = {
    filterValues: FilterValuesValues;
    onChange: (values: FilterValuesValues) => void;
}

export const FilterChannel: FC<Props> = ({filterValues, onChange}) => {
    const translate = useTranslate();
    //const [search, setSearch] = useState<string>();

    const {data: selected} = useChannelsByCodes(filterValues.channel);
    const {data: results, fetchNextPage} = useInfiniteChannels();
    //const {data: results, fetchNextPage} = useInfiniteChannels({search: search});
    const channels = useUniqueEntitiesByCode<Channel>(selected, results);

    return (<>
        <List.Row>
            <List.Cell width="auto">
                <MultiSelectInput
                    value={filterValues.channel}
                    emptyResultLabel={translate('akeneo_catalogs.filter_values.criteria.channel.no_matches')}
                    openLabel={translate('akeneo_catalogs.filter_values.action.open')}
                    removeLabel={translate('akeneo_catalogs.filter_values.action.remove')}
                    placeholder={translate('akeneo_catalogs.filter_values.criteria.channel.placeholder')}
                    onChange={v => onChange({...filterValues, channel: v})}
                    onNextPage={fetchNextPage}
                    //onSearchChange={setSearch}
                    // invalid={isInvalid}
                    data-testid='value'
                >
                    {channels?.map(option => (
                        <MultiSelectInput.Option key={option.code} title={option.label} value={option.code}>
                            {option.label}
                        </MultiSelectInput.Option>
                    ))}
                </MultiSelectInput>
            </List.Cell>
        </List.Row>
    </>);
};
