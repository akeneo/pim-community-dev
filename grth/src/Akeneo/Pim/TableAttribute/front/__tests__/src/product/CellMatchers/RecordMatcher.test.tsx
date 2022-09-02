import {renderHook} from '@testing-library/react-hooks';
import useSearch from '../../../../src/product/CellMatchers/RecordMatcher';
import React, {PropsWithChildren} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../../factories';

jest.mock('../../../../src/fetchers/RecordFetcher');

describe('RecordMatcher', () => {
  it('should match if search text match', () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => (
      <DependenciesProvider>
        <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
          {children}
        </TestAttributeContextProvider>
      </DependenciesProvider>
    );

    const {result} = renderHook(() => useSearch(), {wrapper});
    expect(result.current('nantes00e3cffd_f60e_4a51_925b_d2952bd947e1', 'Nantes', 'city')).toBeTruthy();
  });

  it('should not match if no attribute was given', () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => <DependenciesProvider>{children}</DependenciesProvider>;

    const {result} = renderHook(() => useSearch(), {wrapper});
    expect(result.current('nantes00e3cffd_f60e_4a51_925b_d2952bd947e1', 'Nantes', 'city')).toBeFalsy();
  });
});
