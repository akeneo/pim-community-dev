import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ProcessTrackerApp} from './ProcessTrackerApp';
import {screen} from '@testing-library/react';

jest.mock('./pages/list/List', () => ({
  List: () => <>List</>,
}));

test('it renders job list', () => {
  renderWithProviders(<ProcessTrackerApp />);

  expect(screen.getByText('List')).toBeInTheDocument();
});
