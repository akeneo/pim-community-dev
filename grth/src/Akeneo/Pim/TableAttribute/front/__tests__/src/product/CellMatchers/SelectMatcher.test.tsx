import {renderHook} from '@testing-library/react-hooks';
import useSearch from '../../../../src/product/CellMatchers/SelectMatcher';
import React, {PropsWithChildren} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../../factories';

jest.mock('../../../../src/fetchers/SelectOptionsFetcher');

describe('SelectMatcher', () => {
  it('should match if search text match', () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => (
      <DependenciesProvider>
        <TestAttributeContextProvider attribute={getComplexTableAttribute()}>{children}</TestAttributeContextProvider>
      </DependenciesProvider>
    );

    const {result} = renderHook(() => useSearch(), {wrapper});
    expect(result.current('B', 'B', 'nutrition_score')).toBeTruthy();
  });

  it('should not match if no attribute was given', () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => <DependenciesProvider>{children}</DependenciesProvider>;

    const {result} = renderHook(() => useSearch(), {wrapper});
    expect(result.current('B', 'B', 'nutrition_score')).toBeFalsy();
  });
});
