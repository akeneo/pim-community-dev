import React from 'react';
import ReactDOM from 'react-dom';
import {act, getByRole, getByText, fireEvent} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {UnitRow} from 'akeneomeasure/pages/edit/unit-tab/UnitRow';

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

let container;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('It displays a unit row', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <table>
          <tbody>
            <UnitRow unit={unit} isStandardUnit={true} isSelected={true} onRowSelected={() => {}} />
          </tbody>
        </table>
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(getByRole(container, 'unit-row')).toBeInTheDocument();
  expect(getByText(container, 'SQUARE_METER')).toBeInTheDocument();
});

test('It selects the row when clicking on it', async () => {
  let isSelected = false;
  const onRowSelected = jest.fn(() => {
    isSelected = !isSelected;
  });

  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <table>
          <tbody>
            <UnitRow unit={unit} isStandardUnit={true} isSelected={isSelected} onRowSelected={onRowSelected} />
          </tbody>
        </table>
      </AkeneoThemeProvider>,
      container
    );
  });

  await act(async () => {
    const row = getByRole(container, 'unit-row');
    fireEvent.click(row);
  });

  expect(onRowSelected).toBeCalled();
  expect(isSelected).toBe(true);
});

test('It displays an error badge if it is invalid', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <table>
          <tbody>
            <UnitRow unit={unit} isStandardUnit={true} invalid={true} onRowSelected={() => {}} />
          </tbody>
        </table>
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(getByRole(container, 'error-badge')).toBeInTheDocument();
});
