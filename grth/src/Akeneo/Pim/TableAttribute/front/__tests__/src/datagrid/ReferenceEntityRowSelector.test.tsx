import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../factories';
import {mockScroll} from '../../shared/mockScroll';
import {ReferenceEntityRowSelector} from '../../../src/datagrid/ReferenceEntityRowSelector';

jest.mock('../../../src/fetchers/RecordFetcher');
const scroll = mockScroll();

describe('ReferenceEntityRowSelector', () => {
  it('should display current row', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <ReferenceEntityRowSelector value={'lannion00893335_2e73_41e3_ac34_763fb6a35107'} onChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
  });

  it('should display all rows, then update it', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <ReferenceEntityRowSelector value={'lannion00893335_2e73_41e3_ac34_763fb6a35107'} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();

    await act(async () => {
      fireEvent.click(await screen.findByTitle('pim_common.open'));
    });
    act(() => scroll());
    expect(await screen.findAllByText('Lannion')).toHaveLength(2);
    expect(await screen.findByTitle('vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3')).toBeInTheDocument();
    fireEvent.click(await screen.findByTitle('vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3'));
    expect(handleChange).toBeCalledWith('vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3');
  });

  it('should remove current row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <ReferenceEntityRowSelector value={'lannion00893335_2e73_41e3_ac34_763fb6a35107'} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
    fireEvent.click(await screen.findByTitle('pim_common.clear_value'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should select any row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <ReferenceEntityRowSelector value={'lannion00893335_2e73_41e3_ac34_763fb6a35107'} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
    await act(async () => {
      fireEvent.click(await screen.findByTitle('pim_common.open'));
    });
    fireEvent.click(await screen.findByText('pim_table_attribute.datagrid.any_row'));
    expect(handleChange).toBeCalledWith(null);
  });
});
