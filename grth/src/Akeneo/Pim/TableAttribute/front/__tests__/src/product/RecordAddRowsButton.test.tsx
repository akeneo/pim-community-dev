import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {RecordAddRowsButton, ReferenceEntityColumnDefinition} from '../../../src';
import {act, fireEvent, screen} from '@testing-library/react';
import {getComplexTableAttribute} from '../../factories';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import userEvent from '@testing-library/user-event';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/fetchers/RecordFetcher');
const scroll = mockScroll();

const tableAttributeReferenceEntity = getComplexTableAttribute('reference_entity');

const openDropdown = async () => {
  const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
  await act(async () => {
    fireEvent.click(button);
    expect(await screen.findByText('Vannes')).toBeInTheDocument();
  });
};

describe('RecordAddRowsButton', () => {
  it('should render the component', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeReferenceEntity}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} itemsPerPage={3} />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    userEvent.click(screen.getByTestId('checkbox-0'));
    expect(toggleChange).toBeCalledWith('lannion00893335_2e73_41e3_ac34_763fb6a35107');
  });

  it('should not retrieve on scroll when result length is smaller than itemsPerPage', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeReferenceEntity}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} itemsPerPage={3} />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    act(() => scroll());

    expect(await screen.findByText('Brest')).toBeInTheDocument();
    expect(screen.getByTestId('item_collection').children.length).toBe(5);

    act(() => scroll());
    expect(screen.getByTestId('item_collection').children.length).toBe(5);
  });

  it('should narrow select result to search input', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeReferenceEntity}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    await userEvent.type(screen.getByTitle('pim_table_attribute.product_edit_form.search'), 'Coueron');

    expect(await screen.findByText('Coueron')).toBeInTheDocument();
    expect(screen.getByTestId('item_collection').children.length).toBe(1);
  });

  it('should show no options', async () => {
    const tableAttributeRecordWithoutRecord = getComplexTableAttribute('reference_entity');
    (
      tableAttributeRecordWithoutRecord.table_configuration[0] as ReferenceEntityColumnDefinition
    ).reference_entity_identifier = 'empty_reference_entity';
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeRecordWithoutRecord}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={jest.fn()} itemsPerPage={3} />
      </TestAttributeContextProvider>
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    act(() => {
      fireEvent.click(button);
    });

    expect(await screen.findByText('pim_table_attribute.form.product.no_options')).toBeInTheDocument();
  });

  it('should show no result', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeReferenceEntity}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} itemsPerPage={3} />
      </TestAttributeContextProvider>
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    act(() => {
      fireEvent.click(button);
    });

    await userEvent.type(screen.getByTitle('pim_table_attribute.product_edit_form.search'), 'Unknown');
    expect(await screen.findByText('pim_table_attribute.form.product.no_results')).toBeInTheDocument();
  });

  it('should disable options when maxRowCount is reached', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeReferenceEntity}>
        <RecordAddRowsButton
          checkedOptionCodes={['lannion00893335_2e73_41e3_ac34_763fb6a35107']}
          toggleChange={jest.fn()}
          maxRowCount={1}
        />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    const [lannionCheckbox, vannesCheckbox] = screen.getAllByRole('checkbox');

    expect(lannionCheckbox).toBeChecked();
    expect(lannionCheckbox).toBeEnabled();
    expect(vannesCheckbox).not.toBeChecked();
    expect(vannesCheckbox).toHaveAttribute('readOnly');
  });
});
