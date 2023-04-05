import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Helper, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useMeasurements} from '../../../../hooks/useMeasurements';

const DropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    unit: string | null;
    onChange: (newUnit: string) => void;
    error: string | undefined;
    measurementFamily: string;
};

export const SelectMeasurementUnitDropdown: FC<Props> = ({unit, onChange, error, measurementFamily}) => {
    const translate = useTranslate();
    const {data: measurementUnits} = useMeasurements(measurementFamily);
    return (
        <>
            <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.unit.label')}>
                <SelectInput
                    value={unit}
                    onChange={onChange}
                    clearable={false}
                    invalid={error !== undefined}
                    emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                    openLabel={translate('akeneo_catalogs.common.select.open')}
                    placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.unit.placeholder')}
                    data-testid='metric-source-parameter-unit-dropdown'
                >
                    {measurementUnits?.map(unit => (
                        <option key={unit.code} title={unit.label} value={unit.code}>
                            {unit.label}
                        </option>
                    ))}
                </SelectInput>
                {undefined !== error && (
                    <Helper inline level='error'>
                        {error}
                    </Helper>
                )}
            </DropdownField>
            <Helper inline level='info'>
                {translate('akeneo_catalogs.product_mapping.source.parameters.unit.helper')}
            </Helper>
        </>
    );
};
