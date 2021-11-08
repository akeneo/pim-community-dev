import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {AddRowsButton} from '../../../src/product';
import {getComplexTableAttribute} from '../../factories';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/attribute/ManageOptionsModal');

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
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <AddRowsButton columnCode={'ingredient'} checkedOptionCodes={['salt', 'sugar']} toggleChange={() => {}} />
      </TestAttributeContextProvider>
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
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <AddRowsButton columnCode={'ingredient'} checkedOptionCodes={['salt', 'sugar']} toggleChange={toggleChange} />
      </TestAttributeContextProvider>
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
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <AddRowsButton columnCode={'ingredient'} checkedOptionCodes={['salt', 'sugar']} toggleChange={toggleChange} />
      </TestAttributeContextProvider>
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
    const attribute = getComplexTableAttribute();
    attribute.table_configuration[0].code = 'nutrition_score';
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <AddRowsButton columnCode={'nutrition_score'} checkedOptionCodes={['salt', 'sugar']} toggleChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('A')).toBeInTheDocument();
    });

    expect(screen.queryByText('U')).not.toBeInTheDocument();

    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });
    expect(await screen.findByText('U')).toBeInTheDocument();
  });

  it('should display a message when there is no option', async () => {
    const attribute = getComplexTableAttribute();
    attribute.table_configuration[0].code = 'no_options';
    renderWithProviders(
      <TestAttributeContextProvider attribute={attribute}>
        <AddRowsButton columnCode={'no_options'} checkedOptionCodes={[]} toggleChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('pim_table_attribute.form.product.no_add_options')).toBeInTheDocument();
    });
  });

  it('should open manage options directly', async () => {
    let hasCalledPostAttribute = false;
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_post')) {
        hasCalledPostAttribute = true;
        return Promise.resolve(JSON.stringify(true));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <AddRowsButton columnCode={'ingredient'} checkedOptionCodes={[]} toggleChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    const button = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    await act(async () => {
      fireEvent.click(button);
      expect(await screen.findByText('Sugar')).toBeInTheDocument();
    });

    expect(await screen.findByText('pim_table_attribute.form.product.edit_options')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.form.product.edit_options'));
    fireEvent.click(screen.getByText('Fake confirm'));
    expect(hasCalledPostAttribute).toBeTruthy();
  });
});
