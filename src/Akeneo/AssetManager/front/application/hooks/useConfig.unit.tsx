import React from 'react';
import {renderHook} from '@testing-library/react-hooks';
import {useConfig, ConfigProvider} from './useConfig';

test('It returns the config', () => {
  const {result} = renderHook(() => useConfig(), {
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

  expect(result.current).toEqual({
    value: {some: 'thing'},
  });
});
