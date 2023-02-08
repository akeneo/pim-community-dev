import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../../tests/test-utils';
import {AddPropertyButton} from '../AddPropertyButton';
import userEvent from '@testing-library/user-event';
import {PROPERTY_NAMES, Structure} from '../../../models';

describe('AddPropertyButton', () => {
  it('allows search', async () => {
    render(<AddPropertyButton onAddProperty={jest.fn()} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.structure.property_type.free_text')).toBeInTheDocument();
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
      expect(screen.getByText('pim_identifier_generator.structure.property_type.free_text')).toBeInTheDocument();
    });

    fireEvent.keyDown(searchField, {key: 'Escape', code: 'Escape'});
    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.structure.property_type.free_text')).not.toBeInTheDocument();
    });
  });

  it('adds a property', async () => {
    const onAddProperty = jest.fn();
    render(<AddPropertyButton onAddProperty={onAddProperty} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.structure.property_type.free_text')).toBeInTheDocument();
    });
    expect(screen.getByText('pim_identifier_generator.structure.property_type.free_text')).toBeInTheDocument();
    expect(screen.getByText('pim_identifier_generator.structure.property_type.auto_number')).toBeInTheDocument();

    fireEvent.click(screen.getByText('pim_identifier_generator.structure.property_type.free_text'));
    expect(onAddProperty).toBeCalledWith({
      type: PROPERTY_NAMES.FREE_TEXT,
      string: '',
    });
  });

  it('should not be able to add an auto number twice', () => {
    const structureWithAutoNumber: Structure = [
      {
        type: PROPERTY_NAMES.AUTO_NUMBER,
        digitsMin: 0,
        numberMin: 5
      }
    ];
    render(<AddPropertyButton onAddProperty={jest.fn()} structure={structureWithAutoNumber} />);

    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    expect(screen.getByText('pim_identifier_generator.structure.property_type.free_text')).toBeInTheDocument();
    expect(screen.queryByText('pim_identifier_generator.structure.property_type.auto_number')).not.toBeInTheDocument();
  });
});
