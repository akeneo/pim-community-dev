import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {ManageOptionsModal} from '../../../src/attribute/ManageOptionsModal';
import {getTableAttribute} from '../factories/Attributes';
import {getSelectColumnDefinition} from '../factories/ColumnDefinition';
import {defaultSelectOptions} from '../../../src/fetchers/__mocks__/SelectOptionsFetcher';
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/fetchers/LocaleFetcher');

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

    expect(await screen.findByTestId('code-0')).toHaveValue('salt');
    expect(await screen.findByTestId('label-0')).toHaveValue('Salt');
    expect(await screen.findByTestId('code-1')).toHaveValue('pepper');
    expect(await screen.findByTestId('label-1')).toHaveValue('Pepper');
    expect(await screen.findByTestId('code-2')).toHaveValue('eggs');
    expect(await screen.findByTestId('label-2')).toHaveValue('');
    expect(await screen.findByTestId('code-3')).toHaveValue('sugar');
    expect(await screen.findByTestId('label-3')).toHaveValue('Sugar');
    expect(await screen.findByTestId('code-4')).toHaveValue('');
    expect(await screen.findByTestId('label-4')).toHaveValue('');
    const english = screen.getByLabelText('English (United States)') as HTMLInputElement;
    const german = screen.getByLabelText('German (Germany)') as HTMLInputElement;
    expect(english).toHaveValue('Salt');
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
    expect(await screen.findByTestId('code-0')).toHaveValue('salt');

    fireEvent.change(screen.getByTestId('code-0'), {target: {value: 's a l t'}});
    expect(screen.getByText('pim_table_attribute.validations.invalid_code')).toBeInTheDocument();
    fireEvent.change(screen.getByTestId('code-0'), {target: {value: ''}});
    expect(screen.getByText('pim_table_attribute.validations.column_code_must_be_filled')).toBeInTheDocument();
    fireEvent.change(screen.getByTestId('code-2'), {target: {value: 'pepper'}});
    expect(screen.getAllByText('pim_table_attribute.validations.duplicated_select_code')).toHaveLength(2);
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
    expect(await screen.findByTestId('code-0')).toHaveValue('salt');

    fireEvent.focus(screen.getByTestId('label-4'));
    fireEvent.change(screen.getByTestId('label-4'), {target: {value: 'This is the label!'}});
    fireEvent.blur(screen.getByTestId('label-4'));
    expect(await screen.findByTestId('code-4')).toHaveValue('This_is_the_label_');
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
    expect(await screen.findByTestId('code-0')).toHaveValue('salt');

    fireEvent.click(screen.getByTestId('code-4'));
    fireEvent.change(screen.getByTestId('code-4'), {target: {value: 'code'}});
    fireEvent.change(screen.getByTestId('label-4'), {target: {value: 'label'}});
    fireEvent.change(screen.getByLabelText('German (Germany)'), {target: {value: 'german'}});
    expect(await screen.findByTestId('code-5')).toBeInTheDocument();
    expect(await screen.findByTestId('label-5')).toBeInTheDocument();
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
    expect(await screen.findByTestId('code-0')).toHaveValue('salt');

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
    expect(await screen.findByTestId('code-0')).toHaveValue('fetched_code');
    expect(await screen.findByTestId('label-0')).toHaveValue('Fetched');
    expect(await screen.getByLabelText('German (Germany)')).toHaveValue('Fetshed');
  });

  it('should display options with new attribute', async () => {
    renderWithProviders(
      <ManageOptionsModal
        attribute={getTableAttribute()}
        onChange={jest.fn()}
        columnDefinition={{...getSelectColumnDefinition(), code: 'new_column'}}
        onClose={jest.fn()}
      />
    );
    expect(await screen.findByTestId('code-0')).toHaveValue('');
    expect(await screen.findByTestId('label-0')).toHaveValue('');
  });
});
