import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteChannels} from '../../../../../hooks/useInfiniteChannels';
import {useUniqueEntitiesByCode} from '../../../../../hooks/useUniqueEntitiesByCode';
import {Channel} from '../../../../../models/Channel';
import {useChannel} from '../../../../../hooks/useChannel';
import {Source} from '../../../models/Source';

const DropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
};

export const SelectAssetAttributeChannelDropdown: FC<Props> = ({source, onChange, error}) => {
    const translate = useTranslate();
    const {data: selected} = useChannel(source.parameters?.sub_scope ?? null);
    const {data: results, fetchNextPage} = useInfiniteChannels();
    const channels = useUniqueEntitiesByCode<Channel>(selected ? [selected] : [], results);

    return (
        <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.channel.label')}>
            <SelectInput
                value={source.parameters?.sub_scope ?? null}
                onChange={newChannel => onChange({...source, parameters: {...source.parameters, sub_scope: newChannel}})}
                onNextPage={fetchNextPage}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.channel.placeholder')}
                data-testid='source-parameter-channel-dropdown'
            >
                {channels?.map(channel => (
                    <SelectInput.Option key={channel.code} title={channel.label} value={channel.code}>
                        {channel.label}
                    </SelectInput.Option>
                ))}
            </SelectInput>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </DropdownField>
    );
};
