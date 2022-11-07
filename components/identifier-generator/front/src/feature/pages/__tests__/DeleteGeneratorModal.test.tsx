import React from 'react';
import {DeleteGeneratorModal} from '../DeleteGeneratorModal';
import {render, screen, fireEvent, act, waitFor} from '../../tests/test-utils';
jest.mock('../../hooks/useGetGenerators');

describe('DeleteGeneratorModal', () => {
  it('should should delete a generator', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(),
    } as Response);

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

  it('should should not delete when error occured', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      statusText: 'An unknown error',
      json: () => Promise.resolve(),
    } as Response);

    const onDelete = jest.fn();
    render(<DeleteGeneratorModal onDelete={onDelete} onClose={jest.fn()} generatorCode={'my_generator'} />);

    expect(screen.getByText('pim_common.delete')).toBeDisabled();
    fireEvent.change(screen.getByTitle(''), {target: {value: 'my_generator'}});
    expect(screen.getByText('pim_common.delete')).toBeEnabled();
    fireEvent.click(screen.getByText('pim_common.delete'));

    await waitFor(() => {
      expect(onDelete).not.toBeCalled();
    });
  });

  it('should should close the modal', () => {
    const onClose = jest.fn();
    render(<DeleteGeneratorModal onDelete={jest.fn()} onClose={onClose} generatorCode={'my_generator'} />);

    act(() => {
      fireEvent.click(screen.getByText('pim_common.cancel'));
    });

    expect(onClose).toBeCalled();
  });
});
