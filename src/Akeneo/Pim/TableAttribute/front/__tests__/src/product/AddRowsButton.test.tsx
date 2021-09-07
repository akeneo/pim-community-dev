import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {AddRowsButton} from '../../../src/product';
import {getTableAttribute} from '../factories/Attributes';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;

let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

describe('AddRowsButton', () => {
  it('should render the component', async () => {
    renderWithProviders(
      <AddRowsButton
        attribute={getTableAttribute()}
        columnCode={'ingredient'}
        checkedOptionCodes={['salt', 'sugar']}
        toggleChange={() => {}}
      />
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    const [saltCheckbox, pepperCheckbox, eggsCheckbox, sugarCheckbox] = screen.getAllByRole('checkbox');
    expect(saltCheckbox).toBeInTheDocument();
    expect(saltCheckbox).toBeChecked();
    expect(pepperCheckbox).toBeInTheDocument();
    expect(pepperCheckbox).not.toBeChecked();
    expect(eggsCheckbox).toBeInTheDocument();
    expect(eggsCheckbox).not.toBeChecked();
    expect(sugarCheckbox).toBeInTheDocument();
    expect(sugarCheckbox).toBeChecked();
  });

  it('should trigger the toggleChange function', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <AddRowsButton
        attribute={getTableAttribute()}
        columnCode={'ingredient'}
        checkedOptionCodes={['salt', 'sugar']}
        toggleChange={toggleChange}
      />
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    const [saltCheckbox, pepperCheckbox, eggsCheckbox, sugarCheckbox] = screen.getAllByRole('checkbox');
    expect(saltCheckbox).toBeInTheDocument();
    expect(pepperCheckbox).toBeInTheDocument();
    expect(eggsCheckbox).toBeInTheDocument();
    expect(sugarCheckbox).toBeInTheDocument();

    act(() => {
      fireEvent.click(saltCheckbox);
      fireEvent.click(pepperCheckbox);
    });

    expect(toggleChange).toBeCalledWith('salt');
    expect(toggleChange).toBeCalledWith('pepper');
  });

  it('should search on labels', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <AddRowsButton
        attribute={getTableAttribute()}
        columnCode={'ingredient'}
        checkedOptionCodes={['salt', 'sugar']}
        toggleChange={toggleChange}
      />
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    expect(await screen.findByText('Salt')).toBeInTheDocument();
    expect(await screen.findByText('Pepper')).toBeInTheDocument();

    const searchInput = await screen.findByTitle('pim_table_attribute.product_edit_form.search');
    act(() => {
      fireEvent.change(searchInput, {target: {value: 'Pepp'}});
    });
    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    expect(screen.queryByText('Salt')).not.toBeInTheDocument();
    expect(screen.queryByText('Sugar')).not.toBeInTheDocument();

    act(() => {
      fireEvent.change(searchInput, {target: {value: 'unknown'}});
    });
    expect(screen.queryByText('Salt')).not.toBeInTheDocument();
    expect(screen.queryByText('Sugar')).not.toBeInTheDocument();
    expect(screen.queryByText('Pepper')).not.toBeInTheDocument();
  });

  it('should paginate the options', async () => {
    const toggleChange = jest.fn();
    renderWithProviders(
      <AddRowsButton
        attribute={{...getTableAttribute(), code: 'attribute_with_a_lot_of_options'}}
        columnCode={'ingredient'}
        checkedOptionCodes={['salt', 'sugar']}
        toggleChange={toggleChange}
      />
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('label0')).toBeInTheDocument();
    });

    expect(screen.queryByText('label21')).not.toBeInTheDocument();

    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });
    expect(await screen.findByText('label21')).toBeInTheDocument();
  });

  it('should redirect from helper when there is no option', async () => {
    renderWithProviders(
      <AddRowsButton
        attribute={{...getTableAttribute(), code: 'attribute_without_options'}}
        columnCode={'ingredient'}
        checkedOptionCodes={[]}
        toggleChange={jest.fn()}
      />
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('pim_table_attribute.form.product.no_add_options_link')).toBeInTheDocument();
    });
    fireEvent.click(screen.getByText('pim_table_attribute.form.product.no_add_options_link'));
  });
});
