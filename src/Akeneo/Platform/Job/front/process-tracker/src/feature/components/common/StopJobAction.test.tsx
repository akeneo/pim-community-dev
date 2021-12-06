import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {StopJobAction} from './StopJobAction';

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => {},
  }));
});

test('it confirms the job stop', () => {
  const handleOnStop = jest.fn();
  renderWithProviders(<StopJobAction id="job_id" jobLabel="jobLabel" isStoppable={true} onStop={handleOnStop} />);

  expect(screen.queryByText('pim_datagrid.action.stop.confirmation.content')).not.toBeInTheDocument();

  userEvent.click(screen.getByText('pim_datagrid.action.stop.title'));

  expect(screen.getByText('pim_datagrid.action.stop.confirmation.content')).toBeInTheDocument();

  userEvent.click(screen.getByText('pim_datagrid.action.stop.confirmation.ok'));

  expect(screen.queryByText('pim_datagrid.action.stop.confirmation.content')).not.toBeInTheDocument();
});

test('it does not confirm the job stop if not stoppable', () => {
  const handleOnStop = jest.fn();
  renderWithProviders(<StopJobAction id="job_id" jobLabel="jobLabel" isStoppable={false} onStop={handleOnStop} />);

  expect(screen.queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});
