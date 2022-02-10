import React from 'react';
import {NumberInput} from '../../NumberInput/NumberInput';
import styled from 'styled-components';
import {SelectInput} from '../../SelectInput/SelectInput';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {TableInputContext} from '../TableInputContext';
import {TableInputReadOnlyCell} from '../shared/TableInputReadOnlyCell';
import {highlightCell} from '../shared/highlightCell';

const TableInputMeasurementContainer = styled.div<{highlighted: boolean; inError: boolean} & AkeneoThemedProps>`
  display: flex;
  & > *:nth-child(1) {
    margin-right: -5px;
  }
  & > *:nth-child(2) {
    margin-left: -5px;
  }

  ${highlightCell};
`;

const TableInputMeasurementAmount = styled(NumberInput)`
  height: 39px;
  padding-left: 10px;
  padding-right: 10px;
  border-radius: 0;
  border: none;
  background: none;

  & + div {
    display: none; // Hide arrow buttons
  }
`;

const TableInputMeasurementUnit = styled(SelectInput)`
  & > div {
    background: none;

    & > div:nth-child(1) {
      justify-content: flex-end;
    }

    & > div {
      background: none;
      color: ${getColor('grey', 100)};

      & > input {
        border: none;
        text-align: right;
        padding-right: 38px;
      }
    }
  }
`;

type TableInputMeasurementProps = {
  amount: string;
  unit: string;
  emptyResultLabel: string;
  openLabel: string;
  onChange: (amount: string | undefined, unit: string) => void;
  units: {value: string; label: string; symbol?: string}[];
  highlighted?: boolean;
  inError?: boolean;
};

const TableInputMeasurement: React.FC<TableInputMeasurementProps> = ({
  amount,
  unit,
  emptyResultLabel,
  openLabel,
  onChange,
  units,
  ...rest
}) => {
  const {readOnly} = React.useContext(TableInputContext);

  const handleUnitChange = (unit: string) => {
    onChange(amount, unit);
  };

  const handleAmountChange = (amount: string) => {
    onChange(amount, unit);
  };

  const selectedUnit = units.find(({value}) => value === unit);

  return readOnly ? (
    <TableInputReadOnlyCell>
      {amount} <span>{selectedUnit?.symbol || selectedUnit?.label}</span>
    </TableInputReadOnlyCell>
  ) : (
    <TableInputMeasurementContainer {...rest}>
      <TableInputMeasurementAmount value={amount} onChange={handleAmountChange} />
      <TableInputMeasurementUnit
        value={unit || null}
        emptyResultLabel={emptyResultLabel}
        openLabel={openLabel}
        onChange={handleUnitChange}
        clearable={false}
      >
        {units.map(unit => (
          <SelectInput.Option title={unit.label} key={unit.value} value={unit.value}>
            {unit.symbol || unit.label}
          </SelectInput.Option>
        ))}
      </TableInputMeasurementUnit>
    </TableInputMeasurementContainer>
  );
};

export {TableInputMeasurement};
