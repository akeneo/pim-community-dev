import React, {FC} from 'react';
import {Helper, SelectInput} from 'akeneo-design-system';
import {CompletenessCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteChannels} from '../../hooks/useInfiniteChannels';

type Props = {
    state: CompletenessCriterionState;
    onChange: (state: CompletenessCriterionState) => void;
    error: string | undefined;
};

const CompletenessScopeInput: FC<Props> = ({state, onChange, error}) => {
    const translate = useTranslate();
    const {data: channels, fetchNextPage} = useInfiniteChannels();

    return (
        <>
            <SelectInput
                emptyResultLabel=''
                openLabel=''
                value={state.scope}
                onChange={v => onChange({...state, scope: v})}
                onNextPage={fetchNextPage}
                clearable={false}
                invalid={error !== undefined}
                placeholder={translate('akeneo_catalogs.product_selection.channel')}
                data-testid='scope'
            >
                {channels?.map(channel => (
                    <SelectInput.Option key={channel.code} title={channel.label} value={channel.code}>
                        {channel.label}
                    </SelectInput.Option>
                ))}
            </SelectInput>
            {error !== undefined && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </>
    );
};

export {CompletenessScopeInput};
