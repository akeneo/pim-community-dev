import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../utils';
import {ColumnDetailsPlaceholder, NoSelectedColumn} from './ColumnDetailsPlaceholder';

test('it renders a placeholder when no source is selected', () => {
  renderWithProviders(<ColumnDetailsPlaceholder />);

  expect(screen.getByText(/No source selected for the moment./i)).toBeInTheDocument();
});

test('it renders a placeholder when no column is selected', () => {
  renderWithProviders(<NoSelectedColumn />);

  expect(screen.getByText(/No column selected for the moment./i)).toBeInTheDocument();
});