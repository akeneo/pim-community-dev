import React, {FC, useCallback} from 'react';
import {Source} from '../../models/Source';
import {SourceErrors} from '../../models/SourceErrors';
import {SelectLabelLocaleDropdown} from './SelectLabelLocaleDropdown';
import {SelectCurrencyDropdown} from './SelectCurrencyDropdown';
import {SelectChannelCurrencyDropdown} from './SelectChannelCurrenciesDropdown';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useAttribute} from '../../../../hooks/useAttribute';
import {SourceSectionTitle} from '../SourceSectionTitle';
import {SelectMeasurementUnitDropdown} from './SelectMeasurementUnitDropdown';
import {DefaultValue} from './DefaultValue';
import {Target} from '../../models/Target';

const Information = styled.p`
    font-style: italic;
    margin-top: 10px;
`;

type Props = {
    source: Source;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
    target: Target;
};

export const SourceParameters: FC<Props> = ({source, errors, onChange, target}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(source?.source ?? '');

    const handleParametersChange = useCallback(
        newParameters => onChange({...source, parameters: {...source.parameters, ...newParameters}}),
        [source, onChange]
    );
    const handleDefaultValueChange = useCallback(
        newValue => {
            const newSource = {...source, default: newValue};
            if (undefined === newValue) {
                delete newSource.default;
            }

            onChange(newSource);
        },
        [source, onChange]
    );

    const components = [];

    if (undefined !== attribute && null !== source && undefined !== source.parameters) {
        if (undefined !== source.parameters.label_locale) {
            components.push(
                <SelectLabelLocaleDropdown
                    locale={source.parameters.label_locale}
                    onChange={newLocale => handleParametersChange({label_locale: newLocale})}
                    error={errors?.parameters?.label_locale}
                    disabled={attribute.scopable && source.scope === null}
                    key={'select_label_dropdown'}
                />
            );
        }

        if (undefined !== source.parameters.currency && !attribute.scopable) {
            components.push(
                <SelectCurrencyDropdown
                    currency={source.parameters.currency}
                    onChange={newCurrency => handleParametersChange({currency: newCurrency})}
                    error={errors?.parameters?.currency}
                    key={'select_currency_dropdown'}
                />
            );
        }

        if (undefined !== source.parameters.currency && attribute.scopable) {
            components.push(
                <SelectChannelCurrencyDropdown
                    currency={source.parameters.currency}
                    channel={source.scope}
                    onChange={newCurrency => handleParametersChange({currency: newCurrency})}
                    error={errors?.parameters?.currency}
                    disabled={attribute.scopable && source.scope === null}
                    key={'select_channel_currency_dropdown'}
                />
            );
        }

        if (undefined !== source.parameters.unit) {
            components.push(
                <SelectMeasurementUnitDropdown
                    unit={source.parameters.unit}
                    onChange={newUnit => handleParametersChange({unit: newUnit})}
                    error={errors?.source}
                    measurementFamily={attribute.measurement_family ?? ''}
                    key={'select_channel_measurementunit_dropdown'}
                />
            );
        }
    }

    let targetTypeKey = target.type;

    if (null !== target.format && '' !== target.format) {
        targetTypeKey += `+${target.format}`;
    }

    if (['string', 'boolean', 'number'].includes(targetTypeKey)) {
        components.push(
            <DefaultValue
                value={source.default}
                onChange={handleDefaultValueChange}
                error={errors?.default}
                targetTypeKey={targetTypeKey}
                key={'no_parameters'}
            />
        );
    }

    if (components.length === 0) {
        components.push(
            <Information key={'no_parameters'}>
                {translate('akeneo_catalogs.product_mapping.source.parameters.no_parameters_message')}
            </Information>
        );
    }

    return (
        <>
            <SourceSectionTitle order={2}>
                {translate('akeneo_catalogs.product_mapping.source.parameters.title')}
            </SourceSectionTitle>
            {components}
        </>
    );
};
