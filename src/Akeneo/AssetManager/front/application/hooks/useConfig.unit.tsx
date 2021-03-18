import React from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {useConfig, ConfigProvider} from './useConfig';

test('It returns the config', () => {
  const {result} = renderHook(() => useConfig('value'), {
    wrapper: ({children}) => (
      <ConfigProvider
        config={{
          value: {some: 'thing'},
        }}
      >
        {children}
      </ConfigProvider>
    ),
  });

  expect(result.current).toEqual({some: 'thing'});
});

test('It throws when the config is undefined', () => {
  const {result} = renderHook(() => useConfig('value'), {
    wrapper: ({children}) => <ConfigProvider config={{}}>{children}</ConfigProvider>,
  });

  expect(result.error).toEqual(new Error('Invalid config key'));
});
