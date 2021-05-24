import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import { AddColumnModal } from "../../../src/attribute/AddColumnModal";
import { act, screen, fireEvent } from '@testing-library/react';

describe('AddColumnModal', () => {
  it('should render the component', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate}/>);

    expect(screen.getByText('TODO Add a new column')).toBeInTheDocument();
    expect(screen.getByText('Create')).toBeInTheDocument();
  });

  it('should create default code', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate}/>);
    const codeInput = screen.getByLabelText('TODO Code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('TODO Label') as HTMLInputElement;

    await act(async () => {
      fireEvent.change(labelInput, {target: {value: 'This is the label$'}});
    });

    expect(codeInput.value).toEqual('This_is_the_label_');
  });

  it('should not update code once dirty', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate}/>);
    const codeInput = screen.getByLabelText('TODO Code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('TODO Label') as HTMLInputElement;

    await act(async () => {
      fireEvent.change(codeInput, {target: {value: 'the_code'}});
    });
    await act(async () => {
      fireEvent.change(labelInput, {target: {value: 'This is the label$'}});
    });

    expect(codeInput.value).toEqual('the_code');
  });

  it('should not add column', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate}/>);
    const codeInput = screen.getByLabelText('TODO Code') as HTMLInputElement;
    const labelInput = screen.getByLabelText('TODO Label') as HTMLInputElement;
    const dataTypeInput = screen.getByLabelText('TODO Data type') as HTMLInputElement;
    const createButton = screen.getByText(/Create/) as HTMLButtonElement;

    fireEvent.change(labelInput, {target: {value: 'Ingredients'}});
    fireEvent.change(codeInput, {target: {value: 'ingredients'}});
    fireEvent.focus(dataTypeInput);
    expect(screen.getByText('text')).toBeInTheDocument();
    fireEvent.click(screen.getByText('text'));

    expect(createButton.disabled).toEqual(false);

    await act(async () => {
      fireEvent.click(createButton);
    });

    expect(handleCreate).toHaveBeenCalledWith({
      "code": "ingredients",
      "data_type": "text",
      "labels": {
        "en_US": "Ingredients",
      }
    });
    expect(handleClose).toHaveBeenCalled();
  });

  it('should display validation errors', async () => {
    const handleClose = jest.fn();
    const handleCreate = jest.fn();
    renderWithProviders(<AddColumnModal close={handleClose} onCreate={handleCreate}/>);
    const codeInput = screen.getByLabelText('TODO Code') as HTMLInputElement;
    const dataTypeInput = screen.getByLabelText('TODO Data type') as HTMLInputElement;
    const createButton = screen.getByText(/Create/) as HTMLButtonElement;

    fireEvent.focus(dataTypeInput);
    expect(screen.getByText('text')).toBeInTheDocument();
    fireEvent.click(screen.getByText('text'));

    fireEvent.change(codeInput, {target: {value: 'a wrong code'}});
    expect(screen.getByText('TODO Invalid code')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: ''}});
    expect(screen.getByText('TODO Should not be empty')).toBeInTheDocument();
    expect(createButton.disabled).toEqual(true);

    fireEvent.change(codeInput, {target: {value: 'a_good_code'}});
    expect(createButton.disabled).toEqual(false);
  });
});
