import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {AddRowsButton} from '../../../src';
import {getComplexTableAttribute} from '../../factories';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/attribute/ManageOptionsModal');
jest.mock('../../../src/fetchers/RecordFetcher');
mockScroll();

describe('AddRowsButton', () => {
  it('should render select rows button', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <AddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} />
      </TestAttributeContextProvider>
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });
  });

  it('should render record rows button', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('reference_entity')}>
        <AddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} />
      </TestAttributeContextProvider>
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('Vannes')).toBeInTheDocument();
    });
  });
});
