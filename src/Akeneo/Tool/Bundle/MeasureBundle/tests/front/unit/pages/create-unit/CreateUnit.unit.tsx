'use strict';

import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, screen} from '@testing-library/react';
import {CreateUnit} from 'akeneomeasure/pages/create-unit/CreateUnit';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const changeTextInputValue = (label: string, value: string) => {
  const input = screen.getByLabelText(label, {exact: false, trim: true}) as HTMLInputElement;
  fireEvent.change(input, {target: {value: value}});
};

const changeOperationValue = (index: number, value: string) => {
  const inputs = screen.getAllByPlaceholderText('measurements.unit.operation.placeholder') as HTMLInputElement[];
  fireEvent.change(inputs[index], {target: {value: value}});
};

afterAll(() => {
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
  is_locked: false,
});

test('It renders without errors', () => {
  renderWithProviders(
    <CreateUnit isOpen={true} measurementFamily={measurementFamily} onClose={() => {}} onNewUnit={() => {}} />
  );
});

test('I can fill the fields, validate and the modal is closed.', async () => {
  const mockFetch = jest.fn().mockImplementationOnce(() => ({
    ok: true,
  }));
  const mockOnClose = jest.fn();
  const mockOnNewUnit = jest.fn();

  global.fetch = mockFetch;

  renderWithProviders(
    <CreateUnit isOpen={true} measurementFamily={measurementFamily} onClose={mockOnClose} onNewUnit={mockOnNewUnit} />
  );

  await act(async () => {
    await changeTextInputValue('pim_common.code', 'KILOMETER');
    await changeTextInputValue('pim_common.label', 'Kilometer');
    await changeTextInputValue('measurements.form.input.symbol', 'km');
    await changeOperationValue(0, '10');

    fireEvent.click(screen.getByText('pim_common.add'));
  });

  expect(mockFetch).toHaveBeenCalledWith('akeneo_measurements_validate_unit_rest', {
    body:
      '{"code":"KILOMETER","labels":{"en_US":"Kilometer"},"symbol":"km","convert_from_standard":[{"operator":"mul","value":"10"}]}',
    headers: [
      ['Content-type', 'application/json'],
      ['X-Requested-With', 'XMLHttpRequest'],
    ],
    method: 'POST',
  });
  expect(mockOnNewUnit).toHaveBeenCalledWith({
    code: 'KILOMETER',
    labels: {
      en_US: 'Kilometer',
    },
    symbol: 'km',
    convert_from_standard: [
      {
        operator: 'mul',
        value: '10',
      },
    ],
  });
  expect(mockOnClose).toHaveBeenCalled();
});

test('I can submit invalid values and have the errors displayed.', async () => {
  const errors = Object.freeze([
    {
      propertyPath: 'code',
      message: 'This field can only contain letters, numbers, and underscores.',
      messageTemplate: 'This field can only contain letters, numbers, and underscores.',
      parameters: {},
    },
    {
      propertyPath: 'convert_from_standard[0][value]',
      message: 'The conversion value should be a number',
      messageTemplate: 'The conversion value should be a number',
      parameters: {},
    },
  ]);
  const mockFetch = jest.fn().mockImplementationOnce(() => ({
    ok: false,
    json: () => Promise.resolve(errors),
  }));
  const mockOnClose = jest.fn();
  const mockOnNewUnit = jest.fn();

  global.fetch = mockFetch;

  renderWithProviders(
    <CreateUnit isOpen={true} measurementFamily={measurementFamily} onClose={mockOnClose} onNewUnit={mockOnNewUnit} />
  );

  await act(async () => {
    await changeTextInputValue('pim_common.code', 'invalid unit code');

    fireEvent.click(screen.getByText('pim_common.add'));
  });

  expect(mockFetch).toHaveBeenCalledWith('akeneo_measurements_validate_unit_rest', {
    body:
      '{"code":"invalid unit code","labels":{"en_US":""},"symbol":"","convert_from_standard":[{"operator":"mul","value":""}]}',
    headers: [
      ['Content-type', 'application/json'],
      ['X-Requested-With', 'XMLHttpRequest'],
    ],
    method: 'POST',
  });
  expect(mockOnNewUnit).not.toHaveBeenCalled();
  expect(mockOnClose).not.toHaveBeenCalled();
  expect(screen.getByText('This field can only contain letters, numbers, and underscores.')).toBeInTheDocument();
});
