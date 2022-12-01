import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {CompletenessCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useChannel} from '../../../../hooks/useChannel';
import {Channel} from '../../../../models/Channel';
import {useInfiniteChannels} from '../../../../hooks/useInfiniteChannels';
import {useUniqueEntitiesByCode} from '../../../../hooks/useUniqueEntitiesByCode';

type Props = {
    state: CompletenessCriterionState;
    onChange: (state: CompletenessCriterionState) => void;
    isInvalid: boolean;
};

const CompletenessScopeInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translate = useTranslate();
    const {data: selected} = useChannel(state.scope);
    const {data: results, fetchNextPage} = useInfiniteChannels();
    const channels = useUniqueEntitiesByCode<Channel>(selected ? [selected] : [], results);

    return (
        <SelectInput
            emptyResultLabel={translate('akeneo_catalogs.product_selection.channel.empty')}
            openLabel=''
            value={state.scope}
            onChange={newChannel => onChange({...state, scope: newChannel, locale: null})}
            onNextPage={fetchNextPage}
            clearable={false}
            invalid={isInvalid}
            placeholder={translate('akeneo_catalogs.product_selection.channel.label')}
            data-testid='scope'
        >
            {channels?.map(channel => (
                <SelectInput.Option key={channel.code} title={channel.label} value={channel.code}>
                    {channel.label}
                </SelectInput.Option>
            ))}
        </SelectInput>
    );
};

export {CompletenessScopeInput};
