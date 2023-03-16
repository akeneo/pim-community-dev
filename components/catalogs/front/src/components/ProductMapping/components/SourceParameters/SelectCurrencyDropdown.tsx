import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Source} from '../../models/Source';
import {useCurrencies} from '../../../../hooks/useCurrencies';

const DropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
};
export const SelectCurrencyDropdown: FC<Props> = ({source, onChange, error}) => {
    const translate = useTranslate();
    const {data: currencies} = useCurrencies();

    return (
        <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.currency.label')}>
            <SelectInput
                value={source.parameters?.currency ?? null}
                onChange={newCurrency =>
                    onChange({...source, parameters: {...source.parameters, currency: newCurrency}})
                }
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.currency.placeholder')}
                data-testid='source-parameter-currency-dropdown'
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
