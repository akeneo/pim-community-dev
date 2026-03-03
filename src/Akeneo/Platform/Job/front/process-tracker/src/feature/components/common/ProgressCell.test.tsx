import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ProgressCell} from './ProgressCell';
import {JobExecutionRow} from '../../models';

const jobExecutionRow: JobExecutionRow = {
  job_execution_id: 1,
  started_at: '2020-01-01T00:00:00+00:00',
  tracking: {
    total_step: 2,
    current_step: 2,
    steps: [
      {
        has_error: false,
        warning_count: 0,
        duration: 22,
        is_trackable: true,
        processed_items: 10,
        total_items: 10,
        status: 'COMPLETED',
      },
      {
        has_error: true,
        warning_count: 0,
        duration: 22,
        is_trackable: true,
        processed_items: 2,
        total_items: 10,
        status: 'IN_PROGRESS',
      },
    ],
  },
  has_error: true,
  type: 'export',
  username: 'admin',
  warning_count: 4,
  job_name: 'An export',
  status: 'IN_PROGRESS',
  is_stoppable: true,
};

test('it displays a job execution progress cell', () => {
  renderWithProviders(
    <table>
      <tbody>
        <tr>
          <ProgressCell jobExecutionRow={jobExecutionRow} />
        </tr>
      </tbody>
    </table>
  );

  const [firstStepProgressBar, secondStepProgressBar] = screen.getAllByRole('progressbar');

  expect(firstStepProgressBar).toHaveAttribute('aria-valuenow', '100');
  expect(secondStepProgressBar).toHaveAttribute('aria-valuenow', '20');
  expect(screen.getByTitle('akeneo_job_process_tracker.tracking.in_progress')).toBeInTheDocument();
});

test('it displays indeterminate progress bars when job is starting', () => {
  renderWithProviders(
    <table>
      <tbody>
        <tr>
          <ProgressCell jobExecutionRow={{...jobExecutionRow, status: 'STARTING'}} />
        </tr>
      </tbody>
    </table>
  );

  const [firstStepProgressBar, secondStepProgressBar] = screen.getAllByRole('progressbar');

  expect(firstStepProgressBar).not.toHaveAttribute('aria-valuenow', '100');
  expect(secondStepProgressBar).not.toHaveAttribute('aria-valuenow', '20');
  expect(screen.getByTitle('akeneo_job_process_tracker.tracking.in_progress')).toBeInTheDocument();
});
