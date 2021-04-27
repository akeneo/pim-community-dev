import React, {ReactNode} from 'react';
import {ThemeProvider} from 'styled-components';
import {renderHook} from '@testing-library/react-hooks';
import {useTheme} from './useTheme';
import {pimTheme} from '../theme/pim';

const wrapper = ({children}: {children: ReactNode}) => <ThemeProvider theme={pimTheme}>{children}</ThemeProvider>;

test('It returns the current theme', () => {
  const {result} = renderHook(() => useTheme(), {wrapper});

  expect(result.current).toEqual(pimTheme);
});
