import React, {FC} from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {DependenciesContext} from '../DependenciesContext';
import {renderHookWithProviders} from '../tests/utils';
import {useDateFormatter} from './useDateFormatter';

const options: Intl.DateTimeFormatOptions = {
  dateStyle: 'full',
  timeStyle: 'long',
  timeZone: 'Asia/Shanghai',
  timeZoneName: 'long',
};

const ProviderWithFRLocale: FC = ({children}) => (
  <DependenciesContext.Provider value={{user: {get: () => 'fr_FR', set: jest.fn()}}}>
    {children}
  </DependenciesContext.Provider>
);

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

  expect(format('2021-11-04 15:11:31', options)).toEqual('Thursday, November 4, 2021 at 10:11:31 PM GMT+8');
});

test('it returns a date formatter that is based on the current user context', () => {
  const {result} = renderHook(() => useDateFormatter(), {
    wrapper: ProviderWithFRLocale,
  });

  const format = result.current;

  expect(format('2021-11-04 15:11:31', options)).toEqual('jeudi 4 novembre 2021 à 22:11:31 UTC+8');
});

test('it returns default format when user has no timezone', () => {
  const {result} = renderHook(() => useDateFormatter(), {
    wrapper: ProviderWithNoTimezone,
  });

  const format = result.current;

  expect(format('2021-11-04 15:11:31')).toEqual('11/4/2021, UTC');
});

test('it throws other unexpected errors', () => {
  jest.spyOn(global.Intl, 'DateTimeFormat').mockImplementation(() => {
    throw new Error('Unknown error');
  });

  const {result} = renderHookWithProviders(() => useDateFormatter());

  const format = result.current;

  expect(() => format('2021-11-04 15:11:31')).toThrowError('Unknown error');

  jest.restoreAllMocks();
});
