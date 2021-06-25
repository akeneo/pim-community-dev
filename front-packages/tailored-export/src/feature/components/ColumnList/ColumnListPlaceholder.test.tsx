import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ColumnListPlaceholder} from './ColumnListPlaceholder';

test('it renders a placeholder when no column is selected', () => {
  renderWithProviders(<ColumnListPlaceholder onColumnCreated={jest.fn} />);

  expect(screen.getByText(/akeneo.tailored_export.column_list.no_column_selection.title/i)).toBeInTheDocument();
});

test('it renders a placeholder when no column is selected', () => {
  const handleColumnCreated = jest.fn();
  renderWithProviders(<ColumnListPlaceholder onColumnCreated={handleColumnCreated} />);

  const button = screen.getByText('akeneo.tailored_export.column_list.no_column_selection.add_column');
  fireEvent.click(button);

  expect(handleColumnCreated).toBeCalled();
});
