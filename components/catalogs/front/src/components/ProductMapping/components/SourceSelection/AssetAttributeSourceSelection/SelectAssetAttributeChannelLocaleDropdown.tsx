import React, {FC} from 'react';
import {Helper, Locale, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../../../models/Source';
import {useChannelLocales} from '../../../../../hooks/useChannelLocales';

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
    disabled: boolean;
};

export const SelectAssetAttributeChannelLocaleDropdown: FC<Props> = ({source, onChange, error, disabled}) => {
    const translate = useTranslate();
    const {data: locales} = useChannelLocales(source.parameters?.sub_scope ?? null);

    return (
        <>
            <SelectInput
                value={source.parameters?.sub_locale ?? null}
                onChange={newLocale => onChange({...source, parameters: {...source.parameters, sub_locale: newLocale}})}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.locale.placeholder')}
                data-testid='asset-attribute-channel-locale-dropdown'
                readOnly={disabled}
            >
                {locales?.map(locale => (
                    <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                        <Locale code={locale.code} languageLabel={locale.label} />
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
