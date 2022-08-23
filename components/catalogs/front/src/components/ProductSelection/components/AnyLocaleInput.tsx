import React, {FC} from 'react';
import {Locale, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useLocales} from '../hooks/useLocales';

type LocalizableCriterionState = {
    scope: string | null;
    locale: string | null;
};

type Props = {
    state: LocalizableCriterionState;
    onChange: (state: LocalizableCriterionState) => void;
    isInvalid: boolean;
};

const AnyLocaleInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translate = useTranslate();
    const {data: locales} = useLocales();

    return (
        <SelectInput
            emptyResultLabel={translate('akeneo_catalogs.product_selection.locale.empty')}
            openLabel=''
            value={state.locale}
            onChange={v => onChange({...state, locale: v})}
            clearable={false}
            invalid={isInvalid}
            placeholder={translate('akeneo_catalogs.product_selection.locale.label')}
            data-testid='locale'
        >
            {locales?.map(locale => (
                <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                    <Locale code={locale.code} languageLabel={locale.label} />
                </SelectInput.Option>
            ))}
        </SelectInput>
    );
};

export {AnyLocaleInput};
