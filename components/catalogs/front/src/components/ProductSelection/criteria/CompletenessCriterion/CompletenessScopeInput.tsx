import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {CompletenessCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteChannels} from '../../hooks/useInfiniteChannels';

type Props = {
    state: CompletenessCriterionState;
    onChange: (state: CompletenessCriterionState) => void;
    isInvalid: boolean;
};

const CompletenessScopeInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translate = useTranslate();
    const {data: channels, fetchNextPage} = useInfiniteChannels();

    const handleChange = useCallback(
        (newChannel: string) => {
            const locales = channels?.find(channel => channel.code === newChannel)?.locales ?? [];
            const locale = locales.find(locale => locale.code === state.locale) ? state.locale : null;
            onChange({...state, scope: newChannel, locale: locale});
        },
        [channels, onChange, state]
    );

    return (
        <SelectInput
            emptyResultLabel={translate('akeneo_catalogs.product_selection.channel.empty')}
            openLabel=''
            value={state.scope}
            onChange={handleChange}
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
