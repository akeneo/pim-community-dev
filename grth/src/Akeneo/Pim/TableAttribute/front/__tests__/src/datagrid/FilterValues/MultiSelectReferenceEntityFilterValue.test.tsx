import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import MultiSelectReferenceEntityFilterValue from '../../../../src/datagrid/FilterValues/MultiSelectReferenceEntityFilterValue';
import {getComplexTableAttribute} from '../../../factories';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {AttributeContext} from '../../../../src';
import {mockScroll} from '../../../shared/mockScroll';

jest.mock('../../../../src/fetchers/RecordFetcher');
const scroll = mockScroll();

describe('MultiSelectReferenceEntityFilterValue', () => {
  it('should display current value', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('record')}>
        <MultiSelectReferenceEntityFilterValue
          value={['lannion00893335_2e73_41e3_ac34_763fb6a35107', 'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3']}
          onChange={jest.fn()}
          columnCode={'brand'}
        />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
    expect(screen.getByText('Vannes')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    expect(screen.getByText('Nantes')).toBeInTheDocument();
    expect(screen.queryByText('Brest')).not.toBeInTheDocument();
    act(() => scroll());
    expect(await screen.findByText('Brest')).toBeInTheDocument();
  });

  it('should not have options if there is no attribute defined', async () => {
    renderWithProviders(
      <AttributeContext.Provider value={{attribute: undefined, setAttribute: jest.fn()}}>
        <MultiSelectReferenceEntityFilterValue
          value={['lannion00893335_2e73_41e3_ac34_763fb6a35107', 'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3']}
          onChange={jest.fn()}
          columnCode={'brand'}
        />
      </AttributeContext.Provider>
    );

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    expect(await screen.findByText('pim_common.no_result')).toBeInTheDocument();
  });
});
