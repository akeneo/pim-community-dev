import React, {FC} from 'react';
import {Helper, Locale as LocaleLabel, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useUniqueEntitiesByCode} from '../../../../../hooks/useUniqueEntitiesByCode';
import {Source} from '../../../models/Source';
import {useInfiniteLocales} from '../../../../../hooks/useInfiniteLocales';
import {useLocalesByCodes} from '../../../../../hooks/useLocalesByCodes';
import {Locale} from '../../../../../models/Locale';

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
};

export const SelectAssetAttributeLocaleDropdown: FC<Props> = ({source, onChange, error}) => {
    const translate = useTranslate();
    const {data: selected} = useLocalesByCodes([source.parameters?.sub_locale ?? '']);
    const {data: results, fetchNextPage} = useInfiniteLocales();
    const locales = useUniqueEntitiesByCode<Locale>(selected, results);

    return (
        <>
            <SelectInput
                value={source.parameters?.sub_locale ?? null}
                onChange={newLocale => onChange({...source, parameters: {...source.parameters, sub_locale: newLocale}})}
                onNextPage={fetchNextPage}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.locale.placeholder')}
                data-testid='asset-attribute-locale-dropdown'
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
        </>
    );
};
