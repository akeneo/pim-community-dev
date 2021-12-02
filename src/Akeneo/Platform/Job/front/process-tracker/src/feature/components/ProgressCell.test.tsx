import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {JobExecutionRow} from '../models';
import {ProgressCell} from './ProgressCell';

const jobExecutionRow: JobExecutionRow = {
  job_execution_id: 1,
  started_at: '2020-01-01T00:00:00+00:00',
  tracking: {
    total_step: 2,
    current_step: 1,
    steps: [
      {
        has_error: false,
        has_warning: false,
        is_trackable: false,
        processed_items: 10,
        total_items: 10,
        status: 'COMPLETED',
      },
      {
        has_error: true,
        has_warning: false,
        is_trackable: false,
        processed_items: 2,
        total_items: 10,
        status: 'STARTED',
      },
    ],
  },
  error_count: 2,
  type: 'export',
  username: 'admin',
  warning_count: 4,
  job_name: 'An export',
  status: 'STARTED',
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

  expect(screen.getAllByRole('progressbar')).toHaveLength(2);
});
