import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ColumnDetailsPlaceholder, NoSelectedColumn} from './ColumnDetailsPlaceholder';

test('it renders a placeholder when no source is selected', () => {
  renderWithProviders(<ColumnDetailsPlaceholder />);

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_selected.title/i)).toBeInTheDocument();
});

test('it renders a placeholder when no column is selected', () => {
  renderWithProviders(<NoSelectedColumn />);

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.no_column_selected.title/i)).toBeInTheDocument();
});
