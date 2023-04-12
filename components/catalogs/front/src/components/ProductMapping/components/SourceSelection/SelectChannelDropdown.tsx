import React, {FC} from 'react';
import {Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteChannels} from '../../../../hooks/useInfiniteChannels';
import {useUniqueEntitiesByCode} from '../../../../hooks/useUniqueEntitiesByCode';
import {Channel} from '../../../../models/Channel';
import {useChannel} from '../../../../hooks/useChannel';
import {Source} from '../../models/Source';
import styled from 'styled-components';

const Wrapper = styled.div`
    display: flex;
    flex-direction: column;
    flex: 1;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
};

export const SelectChannelDropdown: FC<Props> = ({source, onChange, error}) => {
    const translate = useTranslate();
    const {data: selected} = useChannel(source.scope);
    const {data: results, fetchNextPage} = useInfiniteChannels();
    const channels = useUniqueEntitiesByCode<Channel>(selected ? [selected] : [], results);

    return (
        <Wrapper>
            <SelectInput
                value={source.scope}
                onChange={newChannel => onChange({...source, scope: newChannel, locale: null})}
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
        </Wrapper>
    );
};
