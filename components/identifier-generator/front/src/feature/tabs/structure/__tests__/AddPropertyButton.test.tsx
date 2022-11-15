import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../../tests/test-utils';
import {AddPropertyButton} from '../AddPropertyButton';
import userEvent from '@testing-library/user-event';
import {PROPERTY_NAMES} from '../../../models';

describe('AddPropertyButton', () => {
  it('allows search', async () => {
    render(<AddPropertyButton onAddProperty={jest.fn()} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(
        screen.getByText('pim_identifier_generator.structure.property_type.free_text')
      ).toBeInTheDocument();
    });

    const searchField = screen.getByTitle('pim_common.search');
    expect(searchField).toBeInTheDocument();

    userEvent.type(searchField, 'toto');
    await waitFor(() => {
      const notFoundText = screen.getByText('pim_common.no_search_result');
      expect(notFoundText).toBeInTheDocument();
    });

    userEvent.clear(searchField);
    userEvent.type(searchField, 'free');
    await waitFor(() => {
      expect(
        screen.getByText('pim_identifier_generator.structure.property_type.free_text')
      ).toBeInTheDocument();
    });

    fireEvent.keyDown(searchField, {key: 'Escape', code: 'Escape'});
    await waitFor(() => {
      expect(
        screen.queryByText('pim_identifier_generator.structure.property_type.free_text')
      ).not.toBeInTheDocument();
    });
  });

  it('adds a property', async () => {
    const onAddProperty = jest.fn();
    render(<AddPropertyButton onAddProperty={onAddProperty} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(
        screen.getByText('pim_identifier_generator.structure.property_type.free_text')
      ).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('pim_identifier_generator.structure.property_type.free_text'));
    expect(onAddProperty).toBeCalledWith({
      type: PROPERTY_NAMES.FREE_TEXT,
      string: '',
    });
  });
});
