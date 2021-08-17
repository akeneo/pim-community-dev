import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ColumnListPlaceholder} from './ColumnListPlaceholder';

test('it renders a placeholder when no column is selected', () => {
  renderWithProviders(<ColumnListPlaceholder onColumnCreated={jest.fn()} />);

  expect(screen.getByText(/akeneo.tailored_export.column_list.no_column_selection.title/i)).toBeInTheDocument();
});

test('it calls the add column handler when clicking on the button', () => {
  const handleColumnCreated = jest.fn();

  renderWithProviders(<ColumnListPlaceholder onColumnCreated={handleColumnCreated} />);

  fireEvent.click(screen.getByText('akeneo.tailored_export.column_list.no_column_selection.add_column'));

  expect(handleColumnCreated).toBeCalled();
});
