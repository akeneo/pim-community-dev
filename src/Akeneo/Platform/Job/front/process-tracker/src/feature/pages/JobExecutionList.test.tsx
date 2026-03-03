import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen, within} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {JobExecutionList} from './JobExecutionList';
import {JobExecutionFilter, JobExecutionRow} from '../models';

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
    automation: null,
    type: 'export',
    username: 'admin',
    warning_count: 4,
    job_name: 'Export job',
    status: 'IN_PROGRESS',
    is_stoppable: true,
  },
  {
    job_execution_id: 2,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'import',
    username: 'admin',
    warning_count: 8,
    job_name: 'Import job',
    status: 'COMPLETED',
    is_stoppable: true,
  },
  {
    job_execution_id: 3,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'import',
    username: 'admin',
    warning_count: 8,
    job_name: 'Another import job',
    status: 'STOPPING',
    is_stoppable: true,
  },
  {
    job_execution_id: 4,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'mass-edit',
    username: 'peter',
    warning_count: 5,
    job_name: 'Mass edit',
    status: 'ABANDONED',
    is_stoppable: false,
  },
  {
    job_execution_id: 5,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: true,
    type: 'import',
    username: 'job_automated_peter',
    warning_count: 5,
    job_name: 'Scheduled job',
    status: 'COMPLETED',
    is_stoppable: false,
  },
];

let mockUseManyRows = false;
const mockManyRows: JobExecutionRow[] = [];
for (let i = 1; i <= 51; i++) {
  mockManyRows.push({
    job_execution_id: i,
    started_at: '2020-01-01T00:00:00+00:00',
    tracking: {
      total_step: 2,
      current_step: 1,
      steps: [],
    },
    has_error: true,
    automation: null,
    type: 'export',
    username: 'admin',
    warning_count: 4,
    job_name: `Job ${i}`,
    status: 'IN_PROGRESS',
    is_stoppable: true,
  });
}

jest.mock('@akeneo-pim-community/shared/lib/components/PimView', () => ({
  PimView: () => <></>,
}));

jest.mock('../hooks/useJobExecutionTable', () => ({
  useJobExecutionTable: ({page, size, sort, automation, type, user, status}: JobExecutionFilter) => {
    const rowsToFilter = mockUseManyRows ? mockManyRows : rows;

    const filteredRows = rowsToFilter.filter(
      row =>
        (0 === type.length || type.includes(row.type)) &&
        (0 === status.length || status.includes(row.status)) &&
        (0 === user.length || user.includes(row.username ?? '')) &&
        (null === automation || automation === row.automation)
    );

    const paginatedRows = filteredRows
      .sort((a, b) => {
        return 'ASC' === sort.direction
          ? a[sort.column].localeCompare(b[sort.column])
          : b[sort.column].localeCompare(a[sort.column]);
      })
      .slice((page - 1) * size, (page - 1) * size + size);

    const jobExecutionTable = {
      rows: paginatedRows,
      matches_count: filteredRows.length,
    };

    return [jobExecutionTable, () => jobExecutionTable];
  },
}));

jest.mock('../hooks/useJobExecutionTypes', () => ({
  useJobExecutionTypes: (): string[] => ['import', 'export', 'mass_edit'],
}));

jest.mock('../hooks/useJobExecutionUsers', () => ({
  useJobExecutionUsers: (): string[] => ['admin', 'peter'],
}));

let mockedSize: number = 3;

jest.mock('../models/JobExecutionFilter', () => ({
  ...jest.requireActual('../models/JobExecutionFilter'),
  getDefaultJobExecutionFilter: () => ({
    page: 1,
    size: mockedSize,
    sort: {column: 'job_name', direction: 'ASC'},
    status: [],
    automation: null,
    type: [],
    user: [],
    code: [],
    search: '',
  }),
}));

beforeEach(() => {
  localStorage.clear();
  mockUseManyRows = false;
  mockedSize = 3;
});

test('it renders a breadcrumb', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('pim_menu.tab.activity')).toBeInTheDocument();
  expect(screen.getByText('pim_menu.item.job_tracker')).toBeInTheDocument();
});

test('it renders the matches job execution count in page header', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('pim_enrich.entity.job_execution.page_title.index')).toBeInTheDocument();
});

test('it can filter on the job status', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByLabelText('akeneo_job_process_tracker.status_filter.label:'));
  userEvent.click(
    within(screen.getByRole('listbox')).getByText('akeneo_job_process_tracker.status_filter.in_progress')
  );

  expect(screen.getByText('Export job')).toBeInTheDocument();
  expect(screen.queryByText('Import job')).not.toBeInTheDocument();
});

test('it can filter on the job automation', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByLabelText('akeneo_job_process_tracker.automation_filter.label:'));
  userEvent.click(within(screen.getByRole('listbox')).getByText('akeneo_job_process_tracker.automation_filter.yes'));

  expect(screen.getByText('Scheduled job')).toBeInTheDocument();
});

test('it can filter on the job type', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByLabelText('akeneo_job_process_tracker.type_filter.label:'));
  userEvent.click(within(screen.getByRole('listbox')).getByText('akeneo_job_process_tracker.type_filter.import'));

  expect(screen.getByText('Import job')).toBeInTheDocument();
  expect(screen.queryByText('Export job')).not.toBeInTheDocument();
});

test('it can filter on the job users', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByLabelText('akeneo_job_process_tracker.user_filter.label:'));
  userEvent.click(screen.getAllByText('admin')[screen.getAllByText('admin').length - 1]);

  expect(screen.getByText('Import job')).toBeInTheDocument();
  expect(screen.getByText('Export job')).toBeInTheDocument();
  expect(screen.getByText('Another import job')).toBeInTheDocument();
  expect(screen.queryByText('Mass edit')).not.toBeInTheDocument();
});

test('it can sort on the job name', () => {
  renderWithProviders(<JobExecutionList />);

  expect(screen.getByText('Another import job')).toBeInTheDocument();

  userEvent.click(screen.getByText('akeneo_job_process_tracker.job_execution_list.table.headers.job_name'));

  expect(screen.queryByText('Another import job')).not.toBeInTheDocument();
});

test('it can change page', () => {
  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByTitle('No. 2'));

  expect(screen.queryByText('Import job')).not.toBeInTheDocument();
  expect(screen.queryByText('Export job')).not.toBeInTheDocument();
  expect(screen.getByText('Mass edit')).toBeInTheDocument();
});

test('it prints a warning when no filter is set and page is 50', () => {
  mockedSize = 1;
  mockUseManyRows = true;

  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByTitle('No. 50'));

  expect(screen.getByText('akeneo_job_process_tracker.max_page_without_filter_helper')).toBeInTheDocument();
});

test('it prints a warning when filter is set and page is 50', () => {
  mockedSize = 1;
  mockUseManyRows = true;

  renderWithProviders(<JobExecutionList />);

  userEvent.click(screen.getByLabelText('akeneo_job_process_tracker.user_filter.label:'));
  userEvent.click(screen.getAllByText('admin')[screen.getAllByText('admin').length - 1]);

  userEvent.click(screen.getByTitle('No. 50'));

  expect(screen.getByText('akeneo_job_process_tracker.max_page_without_filter_helper')).toBeInTheDocument();
});

test('it does not display next pagination button when page is 50', () => {
  mockedSize = 1;
  mockUseManyRows = true;

  renderWithProviders(<JobExecutionList />);

  expect(screen.queryByTitle('No. 51')).not.toBeInTheDocument();
});
