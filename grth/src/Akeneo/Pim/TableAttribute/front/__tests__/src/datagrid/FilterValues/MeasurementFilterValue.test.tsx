import React, {PropsWithChildren} from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import MeasurementFilterValue, {useValueRenderer} from '../../../../src/datagrid/FilterValues/MeasurementFilterValue';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../../factories';
import {renderHook} from '@testing-library/react-hooks';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock('../../../../src/fetchers/MeasurementFamilyFetcher');

describe('MeasurementFilterValue', () => {
  it('should display current value', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <MeasurementFilterValue
          value={{amount: '42', unit: 'MILLICOULOMB'}}
          onChange={jest.fn()}
          columnCode={'ElectricCharge'}
        />
      </TestAttributeContextProvider>
    );
    expect(await screen.findByText('mC')).toBeInTheDocument();
    expect(screen.getByTitle('42')).toBeInTheDocument();
  });

  it('should display default unit', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <MeasurementFilterValue value={undefined} onChange={jest.fn()} columnCode={'ElectricCharge'} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('mAh')).toBeInTheDocument();
  });

  it('should update unit', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <MeasurementFilterValue value={undefined} onChange={handleChange} columnCode={'ElectricCharge'} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('mAh')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.open'));
    fireEvent.click(await screen.findByText('mC'));
    expect(handleChange).toBeCalledWith({amount: '', unit: 'MILLICOULOMB'});
  });

  it('should update amount', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <MeasurementFilterValue value={undefined} onChange={handleChange} columnCode={'ElectricCharge'} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('mAh')).toBeInTheDocument();
    fireEvent.change(screen.getByRole('spinbutton'), {target: {value: '42'}});
    expect(handleChange).toBeCalledWith({amount: '42', unit: 'MILLIAMPEREHOUR'});
  });

  it('should render measurement value', async () => {
    const wrapper = ({children}: PropsWithChildren<{}>) => (
      <DependenciesProvider>
        <TestAttributeContextProvider attribute={getComplexTableAttribute()}>{children}</TestAttributeContextProvider>
      </DependenciesProvider>
    );

    const {result, waitForNextUpdate} = renderHook(
      () => useValueRenderer({amount: '42', unit: 'MILLICOULOMB'}, 'ElectricCharge'),
      {wrapper}
    );
    await waitForNextUpdate();
    expect(result.current).toEqual('42 mC');
  });
});
