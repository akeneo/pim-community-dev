'use strict';

import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText, getByText} from '@testing-library/react';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {CreateUnit} from 'akeneomeasure/pages/create-unit/CreateUnit';
import {UserContext} from 'akeneomeasure/context/user-context';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const changeTextInputValue = async (container: HTMLElement, label: string, value: string) => {
  const input = getByLabelText(container, label, {exact: false, trim: true}) as HTMLInputElement;
  await fireEvent.change(input, {target: {value: value}});
};

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

const measurementFamily = Object.freeze({
  code: 'custom_metric',
  labels: {
    en_US: 'My custom metric',
  },
  standard_unit_code: 'METER',
  units: [
    {
      code: 'METER',
      labels: {
        en_US: 'Meters',
      },
      symbol: 'm',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    },
  ],
});

const mockUserContext = (key: string) => {
  switch (key) {
    case 'uiLocale':
      return 'en_US';
    default:
      return '';
  }
};

test('It renders without errors', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <CreateUnit
          measurementFamily={measurementFamily}
          onClose={() => {}}
          onNewUnit={() => {}}
        />
      </AkeneoThemeProvider>,
      container
    );
  });
});

test('I can fill the fields, validate and the modal is closed.', async () => {
  const mockFetch = jest.fn().mockImplementationOnce(() => ({
    ok: true,
  }));
  const mockOnClose = jest.fn();
  const mockOnNewUnit = jest.fn();

  global.fetch = mockFetch;

  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UserContext.Provider value={mockUserContext}>
          <CreateUnit
            measurementFamily={measurementFamily}
            onClose={mockOnClose}
            onNewUnit={mockOnNewUnit}
          />
        </UserContext.Provider>
      </AkeneoThemeProvider>,
      container
    );
  });

  await act(async () => {
    await changeTextInputValue(container, 'pim_common.code', 'KILOMETER');
    await changeTextInputValue(container, 'pim_common.label', 'Kilometer');
    await changeTextInputValue(container, 'measurements.form.input.symbol', 'km');

    const button = getByText(container, 'pim_common.add');
    await fireEvent.click(button);
  });

  expect(mockFetch).toHaveBeenCalledWith(
    "akeneo_measurements_validate_unit_rest?measurement_family_code=custom_metric",
    {
      'body': '{"code":"KILOMETER","labels":{"en_US":"Kilometer"},"symbol":"km","convert_from_standard":[{"operator":"mul","value":"1"}]}',
      'headers': [['Content-type', 'application/json'], ['X-Requested-With', 'XMLHttpRequest']],
      'method': 'POST'
    }
  );
  expect(mockOnNewUnit).toHaveBeenCalledWith({
    code: 'KILOMETER',
    labels: {
      en_US: 'Kilometer',
    },
    symbol: 'km',
    convert_from_standard: [
      {
        operator: 'mul',
        value: '1',
      },
    ],
  });
  expect(mockOnClose).toHaveBeenCalled();
});

test('I can submit invalid values and have the errors displayed.', async () => {
  const errors = Object.freeze([
    {
      property: 'code',
      message: 'This field can only contain letters, numbers, and underscores.',
    },
  ]);
  const mockFetch = jest.fn().mockImplementationOnce(() => ({
    ok: false,
    json: () => Promise.resolve({errors}),
  }));
  const mockOnClose = jest.fn();
  const mockOnNewUnit = jest.fn();

  global.fetch = mockFetch;

  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <UserContext.Provider value={mockUserContext}>
          <CreateUnit
            measurementFamily={measurementFamily}
            onClose={mockOnClose}
            onNewUnit={mockOnNewUnit}
          />
        </UserContext.Provider>
      </AkeneoThemeProvider>,
      container
    );
  });

  await act(async () => {
    await changeTextInputValue(container, 'pim_common.code', 'invalid unit code');

    const button = getByText(container, 'pim_common.add');
    await fireEvent.click(button);
  });

  expect(mockFetch).toHaveBeenCalledWith(
    "akeneo_measurements_validate_unit_rest?measurement_family_code=custom_metric",
    {
      'body': '{"code":"invalid unit code","labels":{"en_US":""},"symbol":"","convert_from_standard":[{"operator":"mul","value":"1"}]}',
      'headers': [['Content-type', 'application/json'], ['X-Requested-With', 'XMLHttpRequest']],
      'method': 'POST'
    }
  );
  expect(mockOnNewUnit).not.toHaveBeenCalled();
  expect(mockOnClose).not.toHaveBeenCalled();
  expect(getByText(container, 'This field can only contain letters, numbers, and underscores.')).toBeInTheDocument();
});
