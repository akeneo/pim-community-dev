import React from 'react';
import {fireEvent} from '@testing-library/react';
import {UnitRow} from 'akeneomeasure/pages/edit/unit-tab/UnitRow';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const unit = {
  code: 'SQUARE_METER',
  labels: {
    en_US: 'Square Meter',
  },
  symbol: 'sqm',
  convert_from_standard: [
    {
      operator: 'mul',
      value: '1',
    },
  ],
};

test('It displays a unit row', () => {
  const {getByRole, getByText} = renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} isSelected={true} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(getByRole('unit-row')).toBeInTheDocument();
  expect(getByText('SQUARE_METER')).toBeInTheDocument();
});

test('It selects the row when clicking on it', () => {
  let isSelected = false;
  const onRowSelected = jest.fn(() => {
    isSelected = !isSelected;
  });

  const {getByRole} = renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} isSelected={isSelected} onRowSelected={onRowSelected} />
      </tbody>
    </table>
  );

  fireEvent.click(getByRole('unit-row'));

  expect(onRowSelected).toBeCalled();
  expect(isSelected).toBe(true);
});

test('It displays an error badge if it is invalid', () => {
  const {getByRole} = renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} isInvalid={true} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(getByRole('error-badge')).toBeInTheDocument();
});
