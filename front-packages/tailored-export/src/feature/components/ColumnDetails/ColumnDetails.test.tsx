import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ColumnDetails} from './ColumnDetails';

test('it renders column details', () => {
  renderWithProviders(<ColumnDetails columnConfiguration={null} onColumnChange={jest.fn} noColumns={false} />);

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.title/i)).toBeInTheDocument();
});

test('it renders placeholder when there is no column', () => {
  renderWithProviders(<ColumnDetails columnConfiguration={null} onColumnChange={jest.fn} noColumns={true} />);

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.no_column_selected.title/i)).toBeInTheDocument();
});

test('it renders placeholder when there is no column configuration', () => {
  renderWithProviders(<ColumnDetails columnConfiguration={null} onColumnChange={jest.fn} noColumns={false} />);

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_selected.title/i)).toBeInTheDocument();
});

test('it renders placeholder when there is no source selected', () => {
  const columnConfiguration = {
    sources: [],
  };

  renderWithProviders(
    <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={jest.fn} noColumns={false} />
  );

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_selected.title/i)).toBeInTheDocument();
});
