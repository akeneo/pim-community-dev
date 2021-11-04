import {renderHook} from '@testing-library/react-hooks';
import useSearch from '../../../../src/product/CellMatchers/SelectMatcher';
import React, {PropsWithChildren} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

describe('SelectMatcher', () => {
  it('Should not match if no attribute was given', () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => <DependenciesProvider>{children}</DependenciesProvider>;

    const {result} = renderHook(() => useSearch(), {wrapper});
    expect(result.current('optionCode', 'searchText', 'columnCode')).toBeFalsy();
  });
});
