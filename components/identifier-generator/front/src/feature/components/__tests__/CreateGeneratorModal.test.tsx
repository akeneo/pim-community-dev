import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {CreateGeneratorModal} from '../CreateGeneratorModal';
import {waitFor} from '@testing-library/react';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (key: string) => key,
}));

describe('CreateGeneratorModal', () => {
  beforeEach(() => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      status: 200,
      json: () => Promise.resolve({status: 200}),
    });
  });

  it('should render creation form', async () => {
    const onSave = jest.fn();
    render(<CreateGeneratorModal onClose={jest.fn()} onSave={onSave} />);
    await waitFor(() => screen.getByText('pim_identifier_generator.create.form.title'));

    const confirmButton = screen.getByText('pim_identifier_generator.create.form.confirm');
    const labelInput = screen.getByRole('textbox', {name: 'pim_identifier_generator.create.form.label'});
    const codeInput = screen.getByRole('textbox', {name: 'pim_identifier_generator.create.form.code'});

    // confirm button should be disable
    expect(confirmButton).toBeDisabled();

    // user changes label and automatically changes code
    fireEvent.change(labelInput, {target: {value: 'New label 123'}});
    expect(labelInput).toHaveValue('New label 123');
    await waitFor(() => expect(codeInput).toHaveValue('New_label_123'));

    // confirm button should be enabled
    expect(confirmButton).toBeEnabled();

    // when code is already filled, there is no automatic copy from label to code
    fireEvent.change(codeInput, {target: {value: 'new_code'}});
    fireEvent.change(labelInput, {target: {value: 'Other label'}});
    expect(labelInput).toHaveValue('Other label');
    await waitFor(() => expect(codeInput).toHaveValue('new_code'));

    fireEvent.click(confirmButton);
    expect(onSave).toBeCalledWith({code: 'new_code', labels: {uiLocale: 'Other label'}});
  });

  it('should enable form with only code', async () => {
    const onSave = jest.fn();
    render(<CreateGeneratorModal onClose={jest.fn()} onSave={onSave} />);
    await waitFor(() => screen.getByText('pim_identifier_generator.create.form.title'));

    const confirmButton = screen.getByText('pim_identifier_generator.create.form.confirm');
    const codeInput = screen.getByRole('textbox', {name: 'pim_identifier_generator.create.form.code'});

    // confirm button should be disable
    expect(confirmButton).toBeDisabled();
    expect(codeInput).toHaveValue('');

    fireEvent.change(codeInput, {target: {value: 'new_code'}});
    await waitFor(() => expect(codeInput).toHaveValue('new_code'));

    // confirm button should be enabled
    expect(confirmButton).toBeEnabled();
  });
});
