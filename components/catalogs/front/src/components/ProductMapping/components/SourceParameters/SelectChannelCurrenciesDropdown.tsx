import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useChannelCurrencies} from '../../hooks/useChannelCurrencies';

const DropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    currency: string | null;
    channel: string | null;
    onChange: (newCurrency: string) => void;
    error: string | undefined;
    disabled: boolean;
};

export const SelectChannelCurrencyDropdown: FC<Props> = ({currency, channel, onChange, error, disabled}) => {
    const translate = useTranslate();
    const {data: currencies} = useChannelCurrencies(channel);

    return (
        <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.currency.label')}>
            <SelectInput
                value={currency}
                onChange={onChange}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.currency.placeholder')}
                data-testid='source-parameter-currency-dropdown'
                readOnly={disabled}
            >
                {currencies?.map(currency => (
                    <SelectInput.Option key={currency} title={currency} value={currency}>
                        {currency}
                    </SelectInput.Option>
                ))}
            </SelectInput>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
            <Helper inline level='info'>
                {translate('akeneo_catalogs.product_mapping.source.parameters.currency.helper')}
            </Helper>
        </DropdownField>
    );
};
