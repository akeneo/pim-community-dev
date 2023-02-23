import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Helper, Locale as LocaleLabel, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useUniqueEntitiesByCode} from '../../../hooks/useUniqueEntitiesByCode';
import {Source} from '../models/Source';
import {useInfiniteLocales} from '../../../hooks/useInfiniteLocales';
import {useLocalesByCodes} from '../../../hooks/useLocalesByCodes';
import {Locale} from '../../../models/Locale';

const DropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
    disabled: boolean;
};

export const SelectLabelLocaleDropdown: FC<Props> = ({source, onChange, error, disabled}) => {
    const translate = useTranslate();
    const {data: selected} = useLocalesByCodes([source.parameters?.label_locale ?? '']);
    const {data: results, fetchNextPage} = useInfiniteLocales();
    const locales = useUniqueEntitiesByCode<Locale>(selected, results);

    return (
        <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.label_locale.label')}>
            <SelectInput
                value={source.parameters?.label_locale ?? null}
                onChange={newLocale =>
                    onChange({...source, parameters: {...source.parameters, label_locale: newLocale}})
                }
                onNextPage={fetchNextPage}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.label_locale.placeholder')}
                data-testid='source-parameter-label_locale-dropdown'
                readOnly={disabled}
            >
                {locales?.map(locale => (
                    <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                        <LocaleLabel code={locale.code} languageLabel={locale.label} />
                    </SelectInput.Option>
                ))}
            </SelectInput>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
            <Helper inline level='info'>
                {translate('akeneo_catalogs.product_mapping.source.parameters.label_locale.helper')}
            </Helper>
        </DropdownField>
    );
};
