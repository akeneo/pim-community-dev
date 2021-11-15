import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ProcessTrackerApp} from './ProcessTrackerApp';
import {screen} from '@testing-library/react';

jest.mock('./pages/JobExecutionList', () => ({
  JobExecutionList: () => <>JobExecutionList</>,
}));

test('it renders job execution list', () => {
  renderWithProviders(<ProcessTrackerApp />);

  expect(screen.getByText('JobExecutionList')).toBeInTheDocument();
});
