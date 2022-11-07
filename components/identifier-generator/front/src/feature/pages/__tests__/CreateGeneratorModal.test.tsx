import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {CreateGeneratorModal} from '../';
import {waitFor} from '@testing-library/react';

jest.mock('../../hooks/useIdentifierAttributes');

describe('CreateGeneratorModal', () => {
  it('should render creation form', async () => {
    const onSave = jest.fn();
    render(<CreateGeneratorModal onClose={jest.fn()} onSave={onSave} />);
    await waitFor(() => screen.getByText('pim_identifier_generator.create.form.title'));

    const confirmButton = screen.getByText('pim_common.confirm');
    const labelInput = screen.getByRole('textbox', {name: 'pim_common.label'});
    const codeInput = screen.getByRole('textbox', {name: 'pim_common.code pim_common.required_label'});

    expect(confirmButton).toBeDisabled();

    // user changes label and automatically changes code
    fireEvent.change(labelInput, {target: {value: 'New label 123'}});
    expect(labelInput).toHaveValue('New label 123');
    expect(codeInput).toHaveValue('New_label_123');

    expect(confirmButton).toBeEnabled();

    // when code is already filled, there is no automatic copy from label to code
    fireEvent.change(codeInput, {target: {value: 'new_code'}});
    fireEvent.change(labelInput, {target: {value: 'Other label'}});
    expect(labelInput).toHaveValue('Other label');
    expect(codeInput).toHaveValue('new_code');

    fireEvent.click(confirmButton);
    expect(onSave).toBeCalledWith({
      code: 'new_code',
      conditions: [],
      delimiter: null,
      labels: {en_US: 'Other label'},
      structure: [{type: 'free_text', string: 'AKN'}],
      target: 'sku',
    });
  });

  it('should enable form with only code', async () => {
    const onSave = jest.fn();
    render(<CreateGeneratorModal onClose={jest.fn()} onSave={onSave} />);
    await waitFor(() => screen.getByText('pim_identifier_generator.create.form.title'));

    const confirmButton = screen.getByText('pim_common.confirm');
    const codeInput = screen.getByRole('textbox', {name: 'pim_common.code pim_common.required_label'});

    expect(confirmButton).toBeDisabled();
    expect(codeInput).toHaveValue('');

    fireEvent.change(codeInput, {target: {value: 'new_code'}});
    expect(codeInput).toHaveValue('new_code');

    expect(confirmButton).toBeEnabled();
  });
});
