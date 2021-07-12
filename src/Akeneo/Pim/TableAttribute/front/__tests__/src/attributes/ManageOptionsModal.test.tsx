import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {ManageOptionsModal} from '../../../src/attribute/ManageOptionsModal';
import {getTableAttribute} from '../factories/Attributes';
import {getSelectColumnDefinition} from '../factories/ColumnDefinition';
import {defaultSelectOptions} from '../../../src/fetchers/__mocks__/SelectOptionsFetcher';
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/LocaleFetcher');
jest.mock('../../../src/attribute/LocaleSwitcher');

const findInputs = async (rowIndex: number | string) => {
  const row = await screen.findByTestId(`row-${rowIndex}`);
  const inputs = row.querySelectorAll('input');
  if (inputs.length !== 2) {
    throw new Error(`Expected to have 2 inputs, got ${inputs.length}`);
  }

  return inputs;
};

const findCodeInput = async (rowIndex: number | string) => {
  return (await findInputs(rowIndex))[1];
};

const findLabelInput = async (rowIndex: number | string) => {
  return (await findInputs(rowIndex))[0];
};

const getInputs = (rowIndex: number | string) => {
  const row = screen.getByTestId(`row-${rowIndex}`);
  const inputs = row.querySelectorAll('input');
  if (inputs.length !== 2) {
    throw new Error(`Expected to have 2 inputs, got ${inputs.length}`);
  }

  return inputs;
};

const getCodeInput = (rowIndex: number | string) => {
  return getInputs(rowIndex)[1];
};

const getLabelInput = (rowIndex: number | string) => {
  return getInputs(rowIndex)[0];
};

const queryInputs = (rowIndex: number | string) => {
  const row = screen.queryByTestId(`row-${rowIndex}`);
  if (null == row) {
    return null;
  }
  const inputs = row.querySelectorAll('input');
  if (inputs.length !== 2) {
    throw new Error(`Expected to have 2 inputs, got ${inputs.length}`);
  }

  return inputs;
};

const queryLabelInput = (rowIndex: number | string) => {
  return (queryInputs(rowIndex) ?? [])[0] ?? null;
};

describe('ManageOptionsModal', () => {
  it('should render the component', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );

    expect(await findCodeInput(0)).toHaveValue('salt');
    expect(await findLabelInput(0)).toHaveValue('Salt');
    expect(await findCodeInput(1)).toHaveValue('pepper');
    expect(await findLabelInput(1)).toHaveValue('Pepper');
    expect(await findCodeInput(2)).toHaveValue('eggs');
    expect(await findLabelInput(2)).toHaveValue('');
    expect(await findCodeInput('new')).toHaveValue('');
    fireEvent.click(getLabelInput(0));
    const german = screen.getByLabelText('German (Germany)') as HTMLInputElement;
    expect(german).toHaveValue('Achtzergüntlich');
  });

  it('should display validations', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.change(getCodeInput('new'), {target: {value: 's a l t'}});
    expect(await screen.findByText('pim_table_attribute.validations.invalid_code')).toBeInTheDocument();
    fireEvent.change(getCodeInput(defaultSelectOptions.length), {target: {value: ''}});
    expect(screen.getByText('pim_table_attribute.validations.column_code_must_be_filled')).toBeInTheDocument();
    fireEvent.change(getCodeInput('new'), {target: {value: 'pepper'}});
    expect(screen.getByText('pim_table_attribute.validations.duplicated_select_code')).toBeInTheDocument();
  });

  it('should autofill the code', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.focus(getLabelInput('new'));
    fireEvent.change(getLabelInput('new'), {target: {value: 'This is the label!'}});
    expect(getCodeInput(defaultSelectOptions.length)).toHaveValue('This_is_the_label_');
  });

  it('should add a new option and confirm', async () => {
    const handleChange = jest.fn();
    const handleClose = jest.fn();
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={handleChange}
        columnDefinition={getSelectColumnDefinition()}
        onClose={handleClose}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.change(getCodeInput('new'), {target: {value: 'code'}});
    fireEvent.change(getLabelInput(defaultSelectOptions.length), {target: {value: 'label'}});
    fireEvent.click(getLabelInput(defaultSelectOptions.length));
    fireEvent.change(screen.getByLabelText('German (Germany)'), {target: {value: 'german'}});
    fireEvent.click(screen.getByText('pim_common.confirm'));
    expect(handleChange).toBeCalledWith([
      defaultSelectOptions[0],
      defaultSelectOptions[1],
      defaultSelectOptions[2],
      defaultSelectOptions[3],
      {code: 'code', labels: {en_US: 'label', de_DE: 'german'}},
    ]);
    expect(handleClose).toBeCalledTimes(1);
  });

  it('should remove an option', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={handleChange}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.click(screen.getAllByTitle('pim_common.remove')[0]);
    fireEvent.click(screen.getByText('pim_common.confirm'));

    expect(handleChange).toBeCalledWith([defaultSelectOptions[1], defaultSelectOptions[2], defaultSelectOptions[3]]);
  });

  it('should display already fetched options', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={{
          ...getSelectColumnDefinition(),
          options: [{code: 'fetched_code', labels: {en_US: 'Fetched', de_DE: 'Fetshed'}}],
        }}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('fetched_code');
    expect(await findLabelInput(0)).toHaveValue('Fetched');
    expect(screen.getByLabelText('German (Germany)')).toHaveValue('Fetshed');
  });

  it('should display empty option for a new column', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={{...getSelectColumnDefinition(), code: 'new_column'}}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput('new')).toHaveValue('');
    expect(await findLabelInput('new')).toHaveValue('');
    expect(screen.queryByTestId('row-0')).not.toBeInTheDocument();
  });

  it('should filter the option by search', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );

    expect(await findCodeInput(1)).toHaveValue('pepper');
    const searchInput = await screen.findByPlaceholderText('pim_table_attribute.form.attribute.search_placeholder');
    expect(searchInput).toBeInTheDocument();
    fireEvent.change(searchInput, {target: {value: 'pep'}});
    expect(await findCodeInput(1)).toHaveValue('pepper');
    expect(queryLabelInput(0)).not.toBeInTheDocument();
    expect(queryLabelInput(2)).not.toBeInTheDocument();
  });

  it('should paginate the options list', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={{...getTableAttribute(), code: 'test_pagination'}}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );

    expect(await findCodeInput(19)).toBeInTheDocument();
    expect(queryLabelInput(20)).not.toBeInTheDocument();

    fireEvent.click(screen.getByTitle('No. 2'));
    expect(await findCodeInput(20)).toBeInTheDocument();
    expect(queryLabelInput(19)).not.toBeInTheDocument();
    expect(queryLabelInput(21)).not.toBeInTheDocument(); // there is no 21st element

    // We remove the last element of page 2 -> we should go bavk to the first page
    fireEvent.click(screen.getAllByTitle('pim_common.remove')[0]);
    expect(await findCodeInput(19)).toBeInTheDocument();
  });

  it('should be able to change the default locale', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );

    expect(await findLabelInput(0)).toHaveValue('Salt');
    fireEvent.click(getLabelInput(0));
    expect(screen.getByLabelText('German (Germany)')).toHaveValue('Achtzergüntlich');

    fireEvent.click(await screen.findByText('Fake LocaleSwitcher de_DE'));
    expect(await screen.findByLabelText('English (United States)')).toHaveValue('Salt');
    expect(await findLabelInput(0)).toHaveValue('Achtzergüntlich');
  });
});
