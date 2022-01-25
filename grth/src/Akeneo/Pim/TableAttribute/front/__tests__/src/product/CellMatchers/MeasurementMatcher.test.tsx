import {renderHook} from '@testing-library/react-hooks';
import useSearch from '../../../../src/product/CellMatchers/MeasurementMatcher';
import React, {PropsWithChildren} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../../factories';
import {waitFor} from '@testing-library/react';

jest.mock('../../../../src/fetchers/MeasurementFamilyFetcher');

describe('MeasurementMatcher', () => {
  it('should match if search text match amount', async () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => (
      <DependenciesProvider>
        <TestAttributeContextProvider attribute={getComplexTableAttribute()}>{children}</TestAttributeContextProvider>
      </DependenciesProvider>
    );

    const {result} = renderHook(() => useSearch(), {wrapper});

    await waitFor(() => {
      expect(result.current({amount: '20', unit: 'MILLIAMPEREHOUR'}, '20', 'ElectricCharge')).toBeTruthy();
    });
  });

  it('should match if search text match amount and unit', async () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => (
      <DependenciesProvider>
        <TestAttributeContextProvider attribute={getComplexTableAttribute()}>{children}</TestAttributeContextProvider>
      </DependenciesProvider>
    );

    const {result} = renderHook(() => useSearch(), {wrapper});

    await waitFor(() => {
      expect(result.current({amount: '20', unit: 'MILLIAMPEREHOUR'}, '20 mAh', 'ElectricCharge')).toBeTruthy();
    });
  });

  it('should not match if no attribute was given', () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => <DependenciesProvider>{children}</DependenciesProvider>;

    const {result} = renderHook(() => useSearch(), {wrapper});
    expect(result.current({amount: '20', unit: 'MILLIAMPEREHOUR'}, '20 mAh', 'ElectricCharge')).toBeFalsy();
  });

  it('should not match if cell has no amount or unit', () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => (
      <DependenciesProvider>
        <TestAttributeContextProvider attribute={getComplexTableAttribute()}>{children}</TestAttributeContextProvider>
      </DependenciesProvider>
    );

    const {result} = renderHook(() => useSearch(), {wrapper});
    expect(result.current('wrong format value', '20 mAh', 'ElectricCharge')).toBeFalsy();
  });
});
