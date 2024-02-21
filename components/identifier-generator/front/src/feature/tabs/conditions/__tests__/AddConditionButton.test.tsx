import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../../tests/test-utils';
import {AddConditionButton} from '../AddConditionButton';
import userEvent from '@testing-library/user-event';
import {CONDITION_NAMES, Operator} from '../../../models';

jest.mock('../../../hooks/useGetConditionItems');

describe('AddConditionButton', () => {
  it('allows search', async () => {
    render(<AddConditionButton onAddCondition={jest.fn()} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Enabled')).toBeInTheDocument();
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
      expect(screen.getByText('Enabled')).toBeInTheDocument();
    });

    fireEvent.keyDown(searchField, {key: 'Escape', code: 'Escape'});
    await waitFor(() => {
      expect(screen.queryByText('Enabled')).not.toBeInTheDocument();
    });
  });

  it('adds an enabled condition', async () => {
    const onAddCondition = jest.fn();
    render(<AddConditionButton onAddCondition={onAddCondition} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Enabled')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Enabled'));
    expect(onAddCondition).toBeCalledWith({
      type: CONDITION_NAMES.ENABLED,
    });
  });

  it('adds a family condition', async () => {
    const onAddCondition = jest.fn();
    render(<AddConditionButton onAddCondition={onAddCondition} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Family')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Family'));
    expect(onAddCondition).toBeCalledWith({
      type: CONDITION_NAMES.FAMILY,
      operator: Operator.IN,
      value: [],
    });
  });

  it('adds a categories condition', async () => {
    const onAddCondition = jest.fn();
    render(<AddConditionButton onAddCondition={onAddCondition} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Categories')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Categories'));
    expect(onAddCondition).toBeCalledWith({
      type: CONDITION_NAMES.CATEGORIES,
      operator: Operator.IN,
      value: [],
    });
  });

  it('adds a simple select condition', async () => {
    const onAddCondition = jest.fn();
    render(<AddConditionButton onAddCondition={onAddCondition} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Color')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Color'));
    expect(onAddCondition).toBeCalledWith({
      type: CONDITION_NAMES.SIMPLE_SELECT,
      attributeCode: 'color',
      operator: Operator.IN,
      value: [],
    });
  });

  it('adds a multi select condition', async () => {
    const onAddCondition = jest.fn();
    render(<AddConditionButton onAddCondition={onAddCondition} conditions={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Main color')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Main color'));
    expect(onAddCondition).toBeCalledWith({
      type: CONDITION_NAMES.MULTI_SELECT,
      attributeCode: 'main_color',
      operator: Operator.IN,
      value: [],
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
      expect(screen.getByText('Family')).toBeInTheDocument();
    });

    expect(screen.queryByText('Enabled')).not.toBeInTheDocument();
  });
});
