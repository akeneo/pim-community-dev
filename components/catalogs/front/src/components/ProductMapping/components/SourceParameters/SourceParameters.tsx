import React, {FC} from 'react';
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

const Information = styled.p`
    font-style: italic;
    margin-top: 10px;
`;

type Props = {
    source: Source | null;
    errors: SourceErrors | null;
    onChange: (value: Source) => void;
};

export const SourceParameters: FC<Props> = ({source, errors, onChange}) => {
    const translate = useTranslate();
    const {data: attribute} = useAttribute(source?.source ?? '');
    if (undefined === attribute || null === source || undefined === source.parameters) {
        return (
            <>
                <SourceSectionTitle order={2}>
                    {translate('akeneo_catalogs.product_mapping.source.parameters.title')}
                </SourceSectionTitle>
                <Information key={'no_parameters'}>
                    {translate('akeneo_catalogs.product_mapping.source.parameters.no_parameters_message')}
                </Information>
            </>
        );
    }

    return (
        <>
            <SourceSectionTitle order={2}>
                {translate('akeneo_catalogs.product_mapping.source.parameters.title')}
            </SourceSectionTitle>
            {undefined !== source.parameters.label_locale && (
                <SelectLabelLocaleDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.label_locale}
                    disabled={attribute.scopable && source.scope === null}
                />
            )}
            {undefined !== source.parameters.currency && !attribute.scopable && (
                <SelectCurrencyDropdown source={source} onChange={onChange} error={errors?.parameters?.currency} />
            )}
            {undefined !== source.parameters.currency && attribute.scopable && (
                <SelectChannelCurrencyDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.parameters?.currency}
                    disabled={attribute.scopable && source.scope === null}
                />
            )}
            {undefined !== source.parameters.unit && (
                <SelectMeasurementUnitDropdown
                    source={source}
                    onChange={onChange}
                    error={errors?.source}
                    measurementFamily={attribute?.measurement_family ?? null}
                />
            )}
        </>
    );
};
