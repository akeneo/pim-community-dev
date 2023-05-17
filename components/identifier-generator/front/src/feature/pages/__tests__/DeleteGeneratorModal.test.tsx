import React from 'react';
import {DeleteGeneratorModal} from '../DeleteGeneratorModal';
import {act, fireEvent, render, screen, waitFor} from '../../tests/test-utils';

jest.mock('../../hooks/useGetIdentifierGenerators');

describe('DeleteGeneratorModal', () => {
  it('should delete a generator', async () => {
    const onDelete = jest.fn();

    render(<DeleteGeneratorModal onDelete={onDelete} onClose={jest.fn()} generatorCode={'my_generator'} />);

    expect(screen.getByText('pim_common.delete')).toBeDisabled();
    fireEvent.change(screen.getByTitle(''), {target: {value: 'my_generator'}});
    expect(screen.getByText('pim_common.delete')).toBeEnabled();
    fireEvent.click(screen.getByText('pim_common.delete'));

    await waitFor(() => {
      return expect(onDelete).toBeCalled();
    });
  });

  it('should not delete when error occurred', async () => {
    const onDelete = jest.fn();
    render(<DeleteGeneratorModal onDelete={onDelete} onClose={jest.fn()} generatorCode={'error'} />);

    fireEvent.change(screen.getByTitle(''), {target: {value: 'error'}});
    fireEvent.click(screen.getByText('pim_common.delete'));

    await waitFor(() => {
      expect(onDelete).not.toBeCalled();
    });
  });

  it('should close the modal on cancel', () => {
    const onClose = jest.fn();
    render(<DeleteGeneratorModal onDelete={jest.fn()} onClose={onClose} generatorCode={'my_generator'} />);

    act(() => {
      fireEvent.click(screen.getByText('pim_common.cancel'));
    });

    expect(onClose).toBeCalled();
  });
});
