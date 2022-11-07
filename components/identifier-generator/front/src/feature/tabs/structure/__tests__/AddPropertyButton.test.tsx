import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../../tests/test-utils';
import {AddPropertyButton} from '../AddPropertyButton';
import userEvent from '@testing-library/user-event';

describe('AddPropertyButton', () => {
  it('opens the searchbar', async () => {
    // TODO: Clean this ?
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({status: 200}),
    });

    render(<AddPropertyButton />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.structure.property_type.freetext')).toBeInTheDocument();
    });

    const searchField = screen.getByTitle('pim_identifier_generator.structure.search');
    expect(searchField).toBeInTheDocument();

    userEvent.type(searchField, 'toto');
    await waitFor(() => {
      const notFoundText = screen.getByText('pim_identifier_generator.structure.no_result');
      expect(notFoundText).toBeInTheDocument();
    });

    userEvent.clear(searchField);
    userEvent.type(searchField, 'free');
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.structure.property_type.freetext')).toBeInTheDocument();
    });

    fireEvent.keyDown(searchField, {key: 'Escape', code: 'Escape'});
    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.structure.property_type.freetext')).not.toBeInTheDocument();
    });
  });
});
