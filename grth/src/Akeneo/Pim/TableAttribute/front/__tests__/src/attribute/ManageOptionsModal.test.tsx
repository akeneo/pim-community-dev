import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {ManageOptionsModal} from '../../../src';
import {getComplexTableAttribute, getSelectColumnDefinition} from '../../factories';
import {ingredientsSelectOptions} from '../../../src/fetchers/__mocks__/SelectOptionsFetcher';

jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/LocaleFetcher');
jest.mock('../../../src/attribute/LocaleSwitcher');
jest.mock('../../../src/attribute/ImportOptionsButton');

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
        attribute={getComplexTableAttribute()}
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
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.change(getCodeInput('new'), {target: {value: 's a l t'}});
    expect(await screen.findByText('pim_table_attribute.validations.invalid_column_code')).toBeInTheDocument();
    fireEvent.change(getCodeInput(ingredientsSelectOptions.length), {target: {value: ''}});
    expect(screen.getByText('pim_table_attribute.validations.column_code_must_be_filled')).toBeInTheDocument();
    fireEvent.change(getCodeInput('new'), {target: {value: 'pepper'}});
    expect(screen.getByText('pim_table_attribute.validations.duplicated_select_code')).toBeInTheDocument();
  });

  it('should autofill the code', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.focus(getLabelInput('new'));
    fireEvent.change(getLabelInput('new'), {target: {value: 'This is the label!'}});
    expect(getCodeInput(ingredientsSelectOptions.length)).toHaveValue('This_is_the_label_');
  });

  it('should add a new option and confirm', async () => {
    const handleChange = jest.fn();
    const handleClose = jest.fn();

    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={handleChange}
        columnDefinition={getSelectColumnDefinition()}
        onClose={handleClose}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.change(getCodeInput('new'), {target: {value: 'code'}});
    fireEvent.change(getLabelInput(ingredientsSelectOptions.length), {target: {value: 'label'}});
    fireEvent.click(getLabelInput(ingredientsSelectOptions.length));
    fireEvent.change(screen.getByLabelText('German (Germany)'), {target: {value: 'german'}});
    fireEvent.click(screen.getByText('pim_common.confirm'));
    expect(handleChange).toBeCalledWith([
      ingredientsSelectOptions[0],
      ingredientsSelectOptions[1],
      ingredientsSelectOptions[2],
      ingredientsSelectOptions[3],
      {code: 'code', labels: {en_US: 'label', de_DE: 'german'}},
    ]);
    expect(handleClose).toBeCalledTimes(1);
  });

  it('should remove an option', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={handleChange}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    fireEvent.click(screen.getAllByTitle('pim_common.remove')[0]);
    const confirmationInput = await screen.findByLabelText('pim_table_attribute.form.attribute.please_type');
    expect(confirmationInput).toBeInTheDocument();
    fireEvent.change(confirmationInput, {target: {value: 'salt'}});
    fireEvent.click(screen.getByText('pim_common.delete'));

    expect(await findCodeInput(0)).toHaveValue('pepper');
    fireEvent.click(await screen.findByText('pim_common.confirm'));

    expect(handleChange).toBeCalledWith([
      ingredientsSelectOptions[1],
      ingredientsSelectOptions[2],
      ingredientsSelectOptions[3],
    ]);
  });

  it('should remove a new option', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={handleChange}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    await act(async () => {
      fireEvent.change(getCodeInput('new'), {target: {value: 'code'}});
      fireEvent.change(await findLabelInput(ingredientsSelectOptions.length), {target: {value: 'label'}});
      fireEvent.click(screen.getAllByTitle('pim_common.remove')[4]);
    });

    expect(screen.getAllByTitle('pim_common.remove').length).toBe(4);
    const confirmationInput = screen.queryByLabelText('pim_table_attribute.form.attribute.please_type');
    expect(confirmationInput).not.toBeInTheDocument();
  });

  it('should display already fetched options', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={{
          ...getSelectColumnDefinition(),
          options: [{code: 'fetched_code', labels: {en_US: 'Fetched', de_DE: 'Fetshed'}}],
        }}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('fetched_code');
    const labelInput = await findLabelInput(0);
    expect(labelInput).toHaveValue('Fetched');
    fireEvent.click(labelInput);
    expect(await screen.findByLabelText('German (Germany)')).toHaveValue('Fetshed');
  });

  it('should display empty option for a new column', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
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
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );

    expect(await findCodeInput(1)).toHaveValue('pepper');
    const searchInput = await screen.findByPlaceholderText('pim_common.search');
    expect(searchInput).toBeInTheDocument();
    fireEvent.change(searchInput, {target: {value: 'pep'}});
    expect(await findCodeInput(1)).toHaveValue('pepper');
    expect(queryLabelInput(0)).not.toBeInTheDocument();
    expect(queryLabelInput(2)).not.toBeInTheDocument();
  });

  it('should paginate the options list', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={{...getSelectColumnDefinition(), code: 'nutrition_score'}}
        onClose={jest.fn()}
      />
    );

    expect(await findCodeInput(19)).toBeInTheDocument();
    expect(screen.getAllByRole('textbox')).toHaveLength(43); // 20 * 2 + new code + new label + search
    expect(queryLabelInput(20)).not.toBeInTheDocument();

    fireEvent.click(screen.getByTitle('No. 2'));
    expect(await findCodeInput(20)).toBeInTheDocument();
    expect(queryLabelInput(19)).not.toBeInTheDocument();
    expect(queryLabelInput(21)).not.toBeInTheDocument(); // there is no 21st element

    // We remove the last element of page 2 -> we should go back to the first page
    expect(screen.getAllByRole('textbox')).toHaveLength(5); // 1 * 2 + new code + new label + search
    fireEvent.click(screen.getAllByTitle('pim_common.remove')[0]);
    const confirmationInput = await screen.findByLabelText('pim_table_attribute.form.attribute.please_type');
    expect(confirmationInput).toBeInTheDocument();
    fireEvent.change(confirmationInput, {target: {value: 'U'}});
    expect(screen.getByText('pim_common.delete')).not.toBeDisabled();
    fireEvent.click(screen.getByText('pim_common.delete'));

    expect(await findCodeInput(19)).toBeInTheDocument();
  }, 20000);

  it('should prevent the user from adding a new option when reaching the limit', async () => {
    renderWithProviders(
      <ManageOptionsModal
        limit={22}
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={{...getSelectColumnDefinition(), code: 'nutrition_score'}}
        onClose={jest.fn()}
      />
    );
    expect(screen.queryByText('pim_table_attribute.form.attribute.limit_option_reached')).not.toBeInTheDocument();
    expect(await findLabelInput('new')).toBeInTheDocument();

    fireEvent.change(getCodeInput('new'), {target: {value: 'code'}});

    expect(await screen.findByText('pim_table_attribute.form.attribute.limit_option_reached')).toBeInTheDocument();
    expect(queryLabelInput('new')).not.toBeInTheDocument();
  });

  it('should be able to change the default locale', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
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

  it('should focus on new field when entering Enter', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');

    // Test Enter on code field
    fireEvent.change(getCodeInput('new'), {target: {value: 'a_code_that_will_receive_enter'}});
    const codeInput = await findCodeInput(4);
    expect(codeInput).toHaveValue('a_code_that_will_receive_enter');
    fireEvent.focus(codeInput);
    fireEvent.keyDown(codeInput, {key: 'Enter', code: 'Enter'});
    expect(getCodeInput('new')).toHaveFocus();

    // Test Enter on label field
    fireEvent.change(getLabelInput('new'), {target: {value: 'A label that will receive Enter'}});
    const labelInput = await findLabelInput(5);
    expect(labelInput).toHaveValue('A label that will receive Enter');
    fireEvent.focus(labelInput);
    fireEvent.keyDown(labelInput, {key: 'Enter', code: 'Enter'});
    expect(getLabelInput('new')).toHaveFocus();
  });

  it('should ask confirmation to close and not do anything is user says cancel', async () => {
    const handleClose = jest.fn();
    window.confirm = jest.fn().mockImplementation(() => false);

    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={handleClose}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');
    fireEvent.change(getLabelInput(0), {target: {value: 's a l t'}});

    fireEvent.keyDown(await findCodeInput(0), {key: 'Escape', code: 'Escape'});

    expect(handleClose).not.toBeCalled();
  });

  it('should ask confirmation to close and close if user confirms', async () => {
    const handleClose = jest.fn();
    window.confirm = jest.fn().mockImplementation(() => true);

    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={handleClose}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');
    fireEvent.change(getLabelInput(0), {target: {value: 'sel'}});

    fireEvent.keyDown(await findCodeInput(0), {key: 'Escape', code: 'Escape'});

    expect(handleClose).toBeCalled();
  });

  it('should import new options from existing attribute', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={getSelectColumnDefinition()}
        onClose={jest.fn()}
      />
    );
    expect(await findCodeInput(0)).toHaveValue('salt');
    fireEvent.click(screen.getByText('Fake import button'));

    expect(await findCodeInput(4)).toHaveValue('fakeOption1');
    expect(await findCodeInput(5)).toHaveValue('fakeOption2');
    expect(await findLabelInput(4)).toHaveValue('Fake Option 1 Label English');
    expect(await findLabelInput(5)).toHaveValue('');
  });

  it('should import update options from existing attribute', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getComplexTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={{
          ...getSelectColumnDefinition(),
          code: 'new_column',
          options: [
            {
              code: 'fakeOption1',
              labels: {
                fr_FR: 'Fake Option 1 Label French',
              },
            },
            {
              code: 'fakeOption2',
              labels: {
                en_US: 'Fake Option 2 Label English',
              },
            },
          ],
        }}
        onClose={jest.fn()}
      />
    );

    expect(await findCodeInput(0)).toHaveValue('fakeOption1');
    expect(await findCodeInput(1)).toHaveValue('fakeOption2');
    expect(await findLabelInput(0)).toHaveValue('');
    expect(await findLabelInput(1)).toHaveValue('Fake Option 2 Label English');

    fireEvent.click(screen.getByText('Fake import button'));
    expect(await findCodeInput(0)).toHaveValue('fakeOption1');
    expect(await findCodeInput(1)).toHaveValue('fakeOption2');
    expect(await findLabelInput(0)).toHaveValue('Fake Option 1 Label English');
    expect(await findLabelInput(1)).toHaveValue('');
  });
});
