import React from 'react';
import {fireEvent, render, screen} from '../../tests/test-utils';
import {CreateGeneratorModal} from '../';
import {waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {TEXT_TRANSFORMATION} from '../../models';

jest.mock('../../hooks/useGetIdentifierGenerators');

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
      structure: [],
      target: '',
      text_transformation: TEXT_TRANSFORMATION.NO,
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

    fireEvent.click(confirmButton);
    expect(onSave).toBeCalledWith({
      code: 'new_code',
      conditions: [],
      delimiter: null,
      labels: {},
      structure: [],
      target: '',
      text_transformation: TEXT_TRANSFORMATION.NO,
    });
  });

  it('should limit label string length', () => {
    const onSave = jest.fn();
    render(<CreateGeneratorModal onClose={jest.fn()} onSave={onSave} />);

    const confirmButton = screen.getByText('pim_common.confirm');
    const labelInput = screen.getByRole('textbox', {name: 'pim_common.label'});
    const codeInput = screen.getByRole('textbox', {name: 'pim_common.code pim_common.required_label'});

    expect(confirmButton).toBeDisabled();

    const labelLengthLimit = 255;
    const codeLengthLimit = 100;
    const lorem =
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, ' +
      'sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. ' +
      'Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat ' +
      'consequat sit amet ac ex. ';
    const truncLabel = lorem.substring(0, labelLengthLimit);
    const truncCode = truncLabel.replace(/[^a-zA-Z0-9]/g, '_').substring(0, codeLengthLimit);
    // user changes label and automatically changes code
    userEvent.type(labelInput, lorem);
    expect(labelInput).toHaveValue(truncLabel);
    expect(codeInput).toHaveValue(truncCode);
  });

  it('should validate when user types Enter', async () => {
    const onSave = jest.fn();
    render(<CreateGeneratorModal onClose={jest.fn()} onSave={onSave} />);
    await waitFor(() => screen.getByText('pim_identifier_generator.create.form.title'));

    const labelInput = screen.getByRole('textbox', {name: 'pim_common.label'});

    fireEvent.change(labelInput, {target: {value: 'New label 123'}});

    fireEvent.keyDown(labelInput, {key: 'Enter', code: 'Enter'});
    expect(onSave).toBeCalledWith({
      code: 'New_label_123',
      conditions: [],
      delimiter: null,
      labels: {en_US: 'New label 123'},
      structure: [],
      target: '',
      text_transformation: TEXT_TRANSFORMATION.NO,
    });
  });

  it('should prevent creating a generator with a code already used', () => {
    render(<CreateGeneratorModal onClose={jest.fn()} onSave={jest.fn()} />);

    const confirmButton = screen.getByText('pim_common.confirm');
    const codeInput = screen.getByRole('textbox', {name: 'pim_common.code pim_common.required_label'});

    fireEvent.change(codeInput, {target: {value: 'test'}});
    expect(confirmButton).toBeDisabled();
    expect(screen.getByText('validation.create.code_already_used')).toBeInTheDocument();

    fireEvent.change(codeInput, {target: {value: 'TEST'}});
    expect(confirmButton).toBeDisabled();
    expect(screen.getByText('validation.create.code_already_used')).toBeInTheDocument();

    fireEvent.change(codeInput, {target: {value: 'other_code'}});
    expect(confirmButton).not.toBeDisabled();
    expect(screen.queryByText('validation.create.code_already_used')).not.toBeInTheDocument();
  });
});
