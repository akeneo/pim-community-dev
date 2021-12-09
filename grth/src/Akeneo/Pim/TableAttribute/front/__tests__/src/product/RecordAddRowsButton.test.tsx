import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {RecordAddRowsButton} from '../../../src/product';
import {act, fireEvent, screen} from '@testing-library/react';
import {getComplexTableAttribute} from '../../factories';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import userEvent from '@testing-library/user-event';

jest.mock('../../../src/fetchers/RecordFetcher');

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;
let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

const tableAttributeRecord = getComplexTableAttribute('record');

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
      <TestAttributeContextProvider attribute={tableAttributeRecord}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    userEvent.click(screen.getByTestId('checkbox-0'));
    expect(toggleChange).toBeCalledWith('lanion00893335_2e73_41e3_ac34_763fb6a35107');
  });

  it('should not retrieve on scroll when result length is smaller than itemsPerPage', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeRecord}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} itemsPerPage={3} />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });

    expect(await screen.findByText('Brest')).toBeInTheDocument();
    expect(screen.getByTestId('item_collection').children.length).toBe(5);

    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });
    expect(screen.getByTestId('item_collection').children.length).toBe(5);
  });

  it('should narrow select result to search input', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeRecord}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    await userEvent.type(screen.getByTitle('pim_table_attribute.product_edit_form.search'), 'Coueron');

    expect(await screen.findByText('Coueron')).toBeInTheDocument();
    expect(screen.getByTestId('item_collection').children.length).toBe(1);
  });

  it('should show no options', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeRecord}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} itemsPerPage={0} />
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
      <TestAttributeContextProvider attribute={tableAttributeRecord}>
        <RecordAddRowsButton checkedOptionCodes={[]} toggleChange={toggleChange} itemsPerPage={0} />
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
    const toggleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={tableAttributeRecord}>
        <RecordAddRowsButton
          checkedOptionCodes={['lanion00893335_2e73_41e3_ac34_763fb6a35107']}
          toggleChange={toggleChange}
          maxRowCount={1}
        />
      </TestAttributeContextProvider>
    );

    await openDropdown();

    const [lanionCheckbox, vannesCheckbox] = screen.getAllByRole('checkbox');

    expect(lanionCheckbox).toBeChecked();
    expect(lanionCheckbox).toBeEnabled();
    expect(vannesCheckbox).not.toBeChecked();
    expect(vannesCheckbox).toHaveAttribute('readOnly');
  });
});
