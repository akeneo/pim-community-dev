import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../../tests/test-utils';
import {AddConditionButton} from '../AddConditionButton';
import userEvent from '@testing-library/user-event';
import {CONDITION_NAMES} from '../../../models';

describe('AddConditionButton', () => {
  it('allows search', async () => {
    render(<AddConditionButton onAddCondition={jest.fn()} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.selection.property_type.enabled')).toBeInTheDocument();
    });

    const searchField = screen.getByTitle('pim_common.search');
    expect(searchField).toBeInTheDocument();

    userEvent.type(searchField, 'toto');
    await waitFor(() => {
      const notFoundText = screen.getByText('pim_common.no_search_result');
      expect(notFoundText).toBeInTheDocument();
    });

    userEvent.clear(searchField);
    userEvent.type(searchField, 'enabled');
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.selection.property_type.enabled')).toBeInTheDocument();
    });

    fireEvent.keyDown(searchField, {key: 'Escape', code: 'Escape'});
    await waitFor(() => {
      expect(screen.queryByText('pim_identifier_generator.selection.property_type.enabled')).not.toBeInTheDocument();
    });
  });

  it('adds a condition', async () => {
    const onAddCondition = jest.fn();
    render(<AddConditionButton onAddCondition={onAddCondition} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.selection.property_type.enabled')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('pim_identifier_generator.selection.property_type.enabled'));
    expect(onAddCondition).toBeCalledWith({
      type: CONDITION_NAMES.ENABLED,
    });
  });

  it('should not display limited conditions', async () => {
    const onAddCondition = jest.fn();
    render(
      <AddConditionButton onAddCondition={onAddCondition} conditions={[{type: CONDITION_NAMES.ENABLED, value: true}]} />
    );

    const button = screen.getByRole('button');
    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('pim_identifier_generator.selection.property_type.family')).toBeInTheDocument();
    });

    expect(screen.queryByText('pim_identifier_generator.selection.property_type.enabled')).not.toBeInTheDocument();
  });
});
