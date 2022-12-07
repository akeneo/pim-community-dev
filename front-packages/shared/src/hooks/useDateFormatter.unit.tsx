import React, {FC} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {DependenciesContext} from '../DependenciesContext';
import {renderHookWithProviders} from '../tests/utils';
import {useDateFormatter} from './useDateFormatter';

const options: Intl.DateTimeFormatOptions = {
  dateStyle: 'full',
  timeStyle: 'long',
  timeZone: 'UTC',
};

const ProviderWithNoTimezone: FC = ({children}) => (
  <DependenciesContext.Provider
    value={{user: {get: (key: string) => ('timezone' === key ? 'invalid timeZone' : 'en_US'), set: jest.fn()}}}
  >
    {children}
  </DependenciesContext.Provider>
);

test('it returns a date formatter that is based on the default user context', () => {
  const {result} = renderHookWithProviders(() => useDateFormatter());

  const format = result.current;

  expect(format('2020-01-02T00:00:00+00:00', options)).toEqual('Thursday, January 2, 2020 at 12:00:00 AM UTC');
});

test('it returns default format when user has no timezone', () => {
  const {result} = renderHook(() => useDateFormatter(), {
    wrapper: ProviderWithNoTimezone,
  });

  const format = result.current;

  expect(format('2020-01-02T00:00:00+00:00')).toEqual('1/2/2020, UTC');
});

test('it throws other unexpected errors', () => {
  jest.spyOn(global.Intl, 'DateTimeFormat').mockImplementation(() => {
    throw new Error('Unknown error');
  });

  const {result} = renderHookWithProviders(() => useDateFormatter());

  const format = result.current;

  expect(() => format('2020-01-02T00:00:00+00:00')).toThrowError('Unknown error');

  jest.restoreAllMocks();
});
