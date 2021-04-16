'use strict';

import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {useCreateMeasurementFamilySaver} from 'akeneomeasure/pages/create-measurement-family/hooks/use-create-measurement-family-saver';
import {DependenciesProvider} from '@akeneo-pim-community/shared';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;

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

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It returns a success response when saving', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    ok: true,
  }));

  const {result} = renderHook(() => useCreateMeasurementFamilySaver(), {wrapper});
  const save = result.current;

  expect(await save(measurementFamily)).toEqual({
    success: true,
    errors: [],
  });
});

test('It returns a list of errors when there is a validation problem', async () => {
  const errors = [
    {
      propertyPath: 'code',
      message: 'This field can only contain letters, numbers, and underscores.',
    },
  ];

  global.fetch = jest.fn().mockImplementation(() => ({
    ok: false,
    json: () => Promise.resolve(errors),
  }));

  const {result} = renderHook(() => useCreateMeasurementFamilySaver(), {wrapper});
  const save = result.current;

  expect(await save(measurementFamily)).toEqual({
    success: false,
    errors: errors,
  });
});

test('An error is thrown if the server does not respond correctly', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    ok: false,
  }));

  const {result} = renderHook(() => useCreateMeasurementFamilySaver(), {wrapper});
  const save = result.current;

  expect(save(measurementFamily)).rejects.toThrow();
});
