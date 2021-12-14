import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ProcessTrackerApp} from './ProcessTrackerApp';
import {screen} from '@testing-library/react';
import {createHashHistory} from 'history';

jest.mock('./pages/JobExecutionList', () => ({
  JobExecutionList: () => <>JobExecutionList</>,
}));

jest.mock('./pages/JobExecutionDetail', () => ({
  JobExecutionDetail: () => <>JobExecutionDetail</>,
}));

test('it renders job execution list', () => {
  const history = createHashHistory();
  history.push('/job');

  renderWithProviders(<ProcessTrackerApp />);

  expect(screen.getByText('JobExecutionList')).toBeInTheDocument();
});

test('it renders job execution detail', () => {
  const history = createHashHistory();
  history.push('/job/show/9999');

  renderWithProviders(<ProcessTrackerApp />);

  expect(screen.getByText('JobExecutionDetail')).toBeInTheDocument();
});
