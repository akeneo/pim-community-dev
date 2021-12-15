import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import MultiSelectFilterValue from '../../../../src/datagrid/FilterValues/MultiSelectFilterValue';
import {getComplexTableAttribute} from '../../../factories';
import {TestAttributeContextProvider} from '../../../shared/TestAttributeContextProvider';
import {AttributeContext} from '../../../../src';
import {mockScroll} from '../../../shared/mockScroll';

jest.mock('../../../../src/fetchers/SelectOptionsFetcher');
const scroll = mockScroll();

describe('MultiSelectFilterValue', () => {
  it('should display current value', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <MultiSelectFilterValue value={['F', 'B']} onChange={jest.fn()} columnCode={'nutrition_score'} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('F')).toBeInTheDocument();
    expect(screen.getByText('B')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    expect(screen.getByText('T')).toBeInTheDocument();
    expect(screen.queryByText('U')).not.toBeInTheDocument();
    act(() => scroll());
    expect(screen.getByText('U')).toBeInTheDocument();
  });

  it('should not have options if there is no attribute defined', async () => {
    renderWithProviders(
      <AttributeContext.Provider value={{attribute: undefined, setAttribute: jest.fn()}}>
        <MultiSelectFilterValue value={['F', 'B']} onChange={jest.fn()} columnCode={'nutrition_score'} />
      </AttributeContext.Provider>
    );

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    expect(await screen.findByText('pim_common.no_result')).toBeInTheDocument();
  });
});
