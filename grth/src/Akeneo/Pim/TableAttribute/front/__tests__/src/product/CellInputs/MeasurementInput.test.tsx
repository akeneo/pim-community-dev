import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import MeasurementInput from '../../../../src/product/CellInputs/MeasurementInput';
import {getComplexTableAttribute} from '../../../factories';
import {ColumnDefinition} from '../../../../src/models';
import {screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

jest.mock('../../../../src/fetchers/MeasurementFamilyFetcher');

const tableAttribute = getComplexTableAttribute('reference_entity');
const column = getComplexTableAttribute('reference_entity').table_configuration.find(
  ({data_type}) => data_type === 'measurement'
);
const row = {
  'unique id': 'test',
  text: 'toto',
  ElectricCharge: {
    amount: '20',
    unit: 'MILLIAMPEREHOUR',
  },
};

describe('MeasurementInput', () => {
  it('should render the component', async () => {
    const onChange = jest.fn();
    renderWithProviders(
      <MeasurementInput
        row={row}
        columnDefinition={column as ColumnDefinition}
        onChange={onChange}
        inError={false}
        highlighted={false}
        attribute={tableAttribute}
        setAttribute={jest.fn()}
      />
    );

    const amountInput = await screen.findByTitle('20');
    expect(amountInput).toBeInTheDocument();
    expect(screen.getByText('mAh')).toBeInTheDocument();

    userEvent.click(screen.getByTitle('pim_common.open'));

    expect(await screen.findByText('Ah')).toBeInTheDocument();
    userEvent.click(screen.getByText('Ah'));
    expect(onChange).toBeCalledWith({
      amount: '20',
      unit: 'AMPEREHOUR',
    });

    fireEvent.change(amountInput, {target: {value: ''}});
    expect(onChange).toHaveBeenCalledWith(undefined);
  });
});
