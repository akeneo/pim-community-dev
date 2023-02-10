import React, {FC} from 'react';
import styled from 'styled-components';
import {Field, Helper, Locale, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useUniqueEntitiesByCode} from '../../../hooks/useUniqueEntitiesByCode';
import {Source} from '../models/Source';
import {useInfiniteLocales} from '../../../hooks/useInfiniteLocales';
import {MeasurementUnit} from '../../../models/MeasurementUnit';
import {useMeasurements} from '../../../hooks/useMeasurements';


const DropdownField = styled(Field)`
    margin-top: 10px;
`;

type Props = {
    source: Source;
    onChange: (source: Source) => void;
    error: string | undefined;
};

export const SelectMeasurementUnitDropdown: FC<Props> = ({source, onChange, error}) => {
    const translate = useTranslate();
    const {data: measurementUnits} = useMeasurements(source.source ?? '');

    return (
        <DropdownField label={translate('akeneo_catalogs.product_mapping.source.parameters.unit.label')}>
            <SelectInput
                value={source.source}
                onChange={newMeasurementFamily => onChange({...source, locale: newMeasurementFamily})}
                clearable={false}
                invalid={error !== undefined}
                emptyResultLabel={translate('akeneo_catalogs.common.select.no_matches')}
                openLabel={translate('akeneo_catalogs.common.select.open')}
                placeholder={translate('akeneo_catalogs.product_mapping.source.parameters.unit.placeholder')}
                data-testid='source-parameter-source-dropdown'
            >
                {measurementUnits?.map(unit => (
                    <SelectInput.Option key={unit.code} title={unit.label} value={unit.code}>
                        {unit.code}
                    </SelectInput.Option>
                ))}
            </SelectInput>
            {undefined !== error && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </DropdownField>
    );
};
