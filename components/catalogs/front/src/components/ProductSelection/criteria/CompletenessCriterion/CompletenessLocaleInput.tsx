import React, {FC} from 'react';
import {Helper, SelectInput} from 'akeneo-design-system';
import {CompletenessCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useInfiniteLocales} from '../../hooks/useInfiniteLocales';

type Props = {
    state: CompletenessCriterionState;
    onChange: (state: CompletenessCriterionState) => void;
    error: string | undefined;
};

const CompletenessLocaleInput: FC<Props> = ({state, onChange, error}) => {
    const translate = useTranslate();
    const {data: locales, fetchNextPage} = useInfiniteLocales();

    return (
        <>
            <SelectInput
                emptyResultLabel=''
                openLabel=''
                value={state.locale}
                onChange={v => onChange({...state, locale: v})}
                onNextPage={fetchNextPage}
                clearable={false}
                invalid={error !== undefined}
                placeholder={translate('akeneo_catalogs.product_selection.locale')}
                data-testid='locale'
            >
                {locales?.map(locale => (
                    <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                        {locale.label}
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

export {CompletenessLocaleInput};
