import React from 'react';
import {MeasurementFamilyCode, MeasurementValue} from '../../models/MeasurementFamily';
import {getColor, NumberInput, SelectInput} from 'akeneo-design-system';
import {getLabel, useTranslate} from '@akeneo-pim-community/shared';
import {useLocaleCode} from '../../contexts';
import {useMeasurementUnits} from '../../attribute/useMeasurementUnits';
import styled from 'styled-components';

const MeasurementFilterInputContainer = styled.div`
  display: flex;
`;

const MeasurementNumberInput = styled(NumberInput)`
  border-right-width: 0;
  & + div {
    display: none; // Hide arrow buttons
  }
`;

const MeasurementSelectInput = styled(SelectInput)`
  input {
    border-left-width: 0;
    text-align: right;
    padding-right: 38px;
  }

  & > div {
    background: none;

    & > div:nth-child(1) {
      justify-content: flex-end;
    }

    & > div {
      background: none;
      color: ${getColor('grey', 100)};
    }
  }
`;

type MeasurementFilterInputProps = {
  value: MeasurementValue;
  onChange: (value: MeasurementValue) => void;
  measurementFamilyCode: MeasurementFamilyCode;
};

const MeasurementFilterInput: React.FC<MeasurementFilterInputProps> = ({value, onChange, measurementFamilyCode}) => {
  const translate = useTranslate();
  const localeCode = useLocaleCode();
  const units = useMeasurementUnits(measurementFamilyCode);
  const {amount, unit} = value;

  const handleAmountChange = (amount: string) => {
    onChange({amount, unit});
  };

  const handleUnitChange = (unit: string | null) => {
    onChange({amount, unit: unit as string});
  };

  const unitsTranslated = React.useMemo(
    () =>
      units?.map(({code, labels, symbol}) => ({
        value: code,
        label: getLabel(labels, localeCode, code),
        symbol,
      })) || [],
    [localeCode, units]
  );

  return (
    <MeasurementFilterInputContainer>
      <MeasurementNumberInput value={amount} onChange={handleAmountChange} />
      <MeasurementSelectInput
        value={unit}
        onChange={handleUnitChange}
        clearable={false}
        emptyResultLabel={translate('pim_common.no_result')}
        openLabel={translate('pim_common.open')}>
        {unitsTranslated.map(unit => {
          return (
            <SelectInput.Option value={unit.value} key={unit.value}>
              {unit.symbol || unit.label}
            </SelectInput.Option>
          );
        })}
      </MeasurementSelectInput>
    </MeasurementFilterInputContainer>
  );
};

export {MeasurementFilterInput};
