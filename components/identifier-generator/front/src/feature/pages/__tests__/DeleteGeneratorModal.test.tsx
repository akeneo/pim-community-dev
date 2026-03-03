import React from 'react';
import {DeleteGeneratorModal} from '../DeleteGeneratorModal';
import {act, fireEvent, mockResponse, render, screen, waitFor} from '../../tests/test-utils';

jest.mock('../../hooks/useGetIdentifierGenerators');

describe('DeleteGeneratorModal', () => {
  it('should delete a generator', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_rest_delete', 'DELETE', {ok: true, json: {}});

    const onDelete = jest.fn();

    render(<DeleteGeneratorModal onDelete={onDelete} onClose={jest.fn()} generatorCode={'my_generator'} />);

    expect(screen.getByText('pim_common.delete')).toBeDisabled();
    fireEvent.change(screen.getByTitle(''), {target: {value: 'my_generator'}});
    expect(screen.getByText('pim_common.delete')).toBeEnabled();
    fireEvent.click(screen.getByText('pim_common.delete'));

    await waitFor(() => {
      return expect(onDelete).toBeCalled();
    });

    expectCall();
  });

  it('should not delete when error occurred', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_rest_delete', 'DELETE', {ok: false, json: {}});

    const onDelete = jest.fn();
    render(<DeleteGeneratorModal onDelete={onDelete} onClose={jest.fn()} generatorCode={'my_generator'} />);

    fireEvent.change(screen.getByTitle(''), {target: {value: 'my_generator'}});
    fireEvent.click(screen.getByText('pim_common.delete'));

    await waitFor(() => {
      expect(onDelete).not.toBeCalled();
    });

    expectCall();
  });

  it('should close the modal', () => {
    const onClose = jest.fn();
    render(<DeleteGeneratorModal onDelete={jest.fn()} onClose={onClose} generatorCode={'my_generator'} />);

    act(() => {
      fireEvent.click(screen.getByText('pim_common.cancel'));
    });

    expect(onClose).toBeCalled();
  });
});
