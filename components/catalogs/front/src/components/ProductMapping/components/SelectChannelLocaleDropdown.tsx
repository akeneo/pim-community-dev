import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Locale, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../models/Source';
import {useChannelLocales} from '../../ProductSelection/hooks/useChannelLocales';

const DropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
};

export const SelectChannelLocaleDropdown: FC<Props> = ({source, onChange, error}) => {
    const translate = useTranslate();
    const {data: locales} = useChannelLocales(source.scope);

    return (
        <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.locale.label')}>
            <SelectInput
                value={source.locale}
                onChange={newLocale => onChange({...source, locale: newLocale})}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.product_mapping.source.parameters.locale.scope_required')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.locale.placeholder')}
                data-testid='source-parameter-channel-locale-dropdown'
            >
                {locales?.map(locale => (
                    <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                        <Locale code={locale.code} languageLabel={locale.label} />
                    </SelectInput.Option>
                ))}
            </SelectInput>
        </DropdownField>
    );
};
