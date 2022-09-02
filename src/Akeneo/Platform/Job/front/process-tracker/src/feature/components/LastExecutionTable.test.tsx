import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {act, screen, within} from '@testing-library/react';
import {LastExecutionTable} from './LastExecutionTable';
import {JobExecutionRow} from '../models';
import userEvent from '@testing-library/user-event';

const rows: JobExecutionRow[] = [
  {
    job_execution_id: 1,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    type: 'export',
    username: 'admin',
    warning_count: 4,
    job_name: 'An export',
    status: 'IN_PROGRESS',
    is_stoppable: true,
  },
  {
    job_execution_id: 2,
    started_at: '2020-01-02T00:00:00+00:00',
    tracking: {
      total_step: 3,
      current_step: 2,
      steps: [],
    },
    has_error: true,
    type: 'export',
    username: 'julia',
    warning_count: 0,
    job_name: 'An export',
    status: 'COMPLETED',
    is_stoppable: true,
  },
  {
    job_execution_id: 3,
    started_at: '2020-01-03T00:00:00+00:00',
    tracking: {
      total_step: 1,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    type: 'export',
    username: 'peter',
    warning_count: 1,
    job_name: 'An export',
    status: 'STARTING',
    is_stoppable: false,
  },
];

let mockedGrantedAcl = ['pim_importexport_export_execution_show', 'pim_importexport_stop_job'];
jest.mock('@akeneo-pim-community/shared/lib/hooks/useSecurity', () => ({
  useSecurity: () => ({
    isGranted: (acl: string) => mockedGrantedAcl.includes(acl),
  }),
}));

const mockRedirect = jest.fn();
jest.mock('@akeneo-pim-community/shared/lib/hooks/useRouter', () => ({
  useRouter: () => ({
    generate: (route: string) => route,
    redirect: mockRedirect,
  }),
}));

beforeEach(() => {
  mockRedirect.mockClear();
  mockedGrantedAcl = ['pim_importexport_export_execution_show', 'pim_importexport_stop_job'];
});

test('it renders the last execution table', () => {
  renderWithProviders(<LastExecutionTable jobExecutionRows={rows} onTableRefresh={jest.fn()} />);

  expect(screen.getByText('admin')).toBeInTheDocument();
  expect(screen.getByText('akeneo_job.job_status.IN_PROGRESS 1/2')).toBeInTheDocument();
  expect(screen.getByText('4')).toBeInTheDocument();

  // One header and 3 rows
  expect(screen.getAllByRole('row')).toHaveLength(4);
});

test('it redirects to a job execution details on row click when user can show detail execution', () => {
  renderWithProviders(<LastExecutionTable jobExecutionRows={rows} onTableRefresh={jest.fn()} />);

  expect(mockRedirect).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('01/03/2020, 12:00 AM'));
  expect(mockRedirect).toHaveBeenCalledWith('akeneo_job_process_tracker_details');
});

test('it does nothing on row click when user cannot show detail execution', () => {
  const importJobExecutionRow: JobExecutionRow = {
    job_execution_id: 2,
    started_at: '2020-01-02T00:00:00+00:00',
    tracking: {
      total_step: 3,
      current_step: 2,
      steps: [],
    },
    has_error: true,
    type: 'import',
    username: 'julia',
    warning_count: 0,
    job_name: 'An import',
    status: 'COMPLETED',
    is_stoppable: true,
  };

  renderWithProviders(<LastExecutionTable jobExecutionRows={[importJobExecutionRow]} onTableRefresh={jest.fn()} />);

  expect(mockRedirect).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('01/02/2020, 12:00 AM'));
  expect(mockRedirect).not.toHaveBeenCalled();
});

test('it redirects to a job execution details on row cmd click', () => {
  const redirectMock = jest.fn();
  jest.spyOn(window, 'open').mockImplementation(url => redirectMock(url));

  renderWithProviders(<LastExecutionTable jobExecutionRows={rows} onTableRefresh={jest.fn()} />);
  expect(redirectMock).not.toHaveBeenCalled();
  userEvent.click(screen.getByText('01/02/2020, 12:00 AM'), {metaKey: true});
  expect(redirectMock).toHaveBeenCalledWith('#akeneo_job_process_tracker_details');
});

test('it can stop a job execution when job execution is stoppable and user have right', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => {},
  }));

  const handleTableRefresh = jest.fn();
  mockedGrantedAcl = ['pim_importexport_stop_job'];

  renderWithProviders(<LastExecutionTable jobExecutionRows={rows} onTableRefresh={handleTableRefresh} />);
  userEvent.click(within(screen.getAllByRole('row')[1]).getByText('pim_datagrid.action.stop.title'));
  await act(async () => {
    await userEvent.click(screen.getByText('pim_datagrid.action.stop.confirmation.ok'));
  });

  expect(handleTableRefresh).toBeCalled();
});

test('it cannot stop a job execution when user does not have right', () => {
  mockedGrantedAcl = [];

  renderWithProviders(<LastExecutionTable jobExecutionRows={rows} onTableRefresh={jest.fn()} />);
  expect(within(screen.getAllByRole('row')[1]).queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});

test('it cannot stop a job execution when job is not stoppable', () => {
  renderWithProviders(<LastExecutionTable jobExecutionRows={rows} onTableRefresh={jest.fn()} />);

  expect(within(screen.getAllByRole('row')[3]).queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});

test('it cannot stop a job execution when job status is not stoppable', () => {
  renderWithProviders(<LastExecutionTable jobExecutionRows={rows} onTableRefresh={jest.fn()} />);

  expect(within(screen.getAllByRole('row')[2]).queryByText('pim_datagrid.action.stop.title')).not.toBeInTheDocument();
});
