import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../utils';
import {ColumnDetails} from './ColumnDetails';

test('it renders column details', () => {
  renderWithProviders(<ColumnDetails columnConfiguration={null} onColumnChange={jest.fn} noColumns={false} />);

  expect(screen.getByText(/Source\(s\)/i)).toBeInTheDocument();
});

test('it renders placeholder when there is no column', () => {
  renderWithProviders(<ColumnDetails columnConfiguration={null} onColumnChange={jest.fn} noColumns={true} />);

  expect(screen.getByText(/No column selected for the moment./i)).toBeInTheDocument();
});

test('it renders placeholder when there is no column configuration', () => {
  renderWithProviders(<ColumnDetails columnConfiguration={null} onColumnChange={jest.fn} noColumns={false} />);

  expect(screen.getByText(/No source selected for the moment./i)).toBeInTheDocument();
});

test('it renders placeholder when there is no source selected', () => {
  const columnConfiguration = {
    sources: []
  };

  renderWithProviders(<ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={jest.fn} noColumns={false} />);

  expect(screen.getByText(/No source selected for the moment./i)).toBeInTheDocument();
});