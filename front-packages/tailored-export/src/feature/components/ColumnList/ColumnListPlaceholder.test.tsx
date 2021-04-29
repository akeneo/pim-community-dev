import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '../../utils';
import {ColumnListPlaceholder} from './ColumnListPlaceholder';

test('it renders a placeholder when no column is selected', () => {
  renderWithProviders(<ColumnListPlaceholder onColumnCreated={jest.fn} />);

  expect(screen.getByText(/No columns selection to export/i)).toBeInTheDocument();
});

test('it renders a placeholder when no column is selected', () => {
  const handleColumnCreated = jest.fn();
  renderWithProviders(<ColumnListPlaceholder onColumnCreated={handleColumnCreated} />);

  const button = screen.getByText('Add first column')
  fireEvent.click(button);

  expect(handleColumnCreated).toBeCalled();
});