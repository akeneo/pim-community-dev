import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
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
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} isSelected={true} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.getByText('SQUARE_METER')).toBeInTheDocument();
  expect(screen.getByText('measurements.family.standard_unit')).toBeInTheDocument();
});

test('It selects the row when clicking on it', () => {
  let isSelected = false;
  const onRowSelected = jest.fn(() => {
    isSelected = !isSelected;
  });

  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} isSelected={isSelected} onRowSelected={onRowSelected} />
      </tbody>
    </table>
  );

  fireEvent.click(screen.getByText('SQUARE_METER'));

  expect(onRowSelected).toBeCalled();
  expect(isSelected).toBe(true);
});

test('It displays an error badge if it is invalid', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} isInvalid={true} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});
