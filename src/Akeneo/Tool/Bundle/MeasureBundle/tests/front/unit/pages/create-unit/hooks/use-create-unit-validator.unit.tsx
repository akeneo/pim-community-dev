'use strict';

import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {useCreateUnitValidator} from 'akeneomeasure/pages/create-unit/hooks/use-create-unit-validator';
import {DependenciesProvider} from '@akeneo-pim-community/shared';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

const measurementFamilyCode = 'Length';
const unit = {
  code: 'METER',
  labels: {
    en_US: 'Meters',
  },
  symbol: 'm',
  convert_from_standard: [
    {
      operator: 'mul',
      value: '1000',
    },
  ],
};
const errors = [
  {
    propertyPath: 'code',
    message: 'This field can only contain letters, numbers, and underscores.',
  },
];

test('It returns a success response if submitted data is valid', async () => {
  global.fetch = jest.fn().mockImplementationOnce(() => ({
    ok: true,
  }));

  const {result} = renderHook(() => useCreateUnitValidator(), {wrapper});
  const validate = result.current;

  expect(await validate(measurementFamilyCode, unit)).toEqual({
    valid: true,
    errors: [],
  });
});

test('It returns a list of errors when there is a validation problem', async () => {
  global.fetch = jest.fn().mockImplementationOnce(() => ({
    ok: false,
    json: () => Promise.resolve(errors),
  }));

  const {result} = renderHook(() => useCreateUnitValidator(), {wrapper});
  const validate = result.current;

  expect(await validate(measurementFamilyCode, unit)).toEqual({
    valid: false,
    errors: errors,
  });
});

test('An error is thrown if the server does not respond correctly', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    ok: false,
  }));

  const {result} = renderHook(() => useCreateUnitValidator(), {wrapper});
  const validate = result.current;

  expect(validate(measurementFamilyCode, unit)).rejects.toThrow();
});
