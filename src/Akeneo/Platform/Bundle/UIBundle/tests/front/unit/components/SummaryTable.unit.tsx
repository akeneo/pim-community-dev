import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {SummaryTable} from '../../../../Resources/public/js/job/execution/summary/SummaryTable';
import {JobExecution} from '../../../../Resources/public/js/job/execution/models';
import {fireEvent} from '@testing-library/dom';

const jobExecution: JobExecution = {
  failures: ['Job interrupted'],
  stepExecutions: [
    {
      label: 'Step with warnings',
      job: 'csv_product_quick_export',
      status: 'Completed',
      summary: {read: '1', written: '1', 'first warnings displayed': '2/2'},
      startedAt: '01/08/2021 12:47 PM',
      endedAt: '01/08/2021 12:47 PM',
      warnings: [
        {
          reason: 'This is a warning, with multiple reasons:\nit whines\na lot\nthis thing',
          item: {foo: 'bar', bar: 'another nice reason'},
        },
        {reason: 'This is a warning with one reason', item: {foo: {bar: {fffff: 'blab blla'}}, bar: 'baz'}},
      ],
      errors: [],
      failures: [],
    },
    {
      label: 'Step with errors',
      job: 'csv_product_quick_export',
      status: 'Completed',
      summary: {},
      startedAt: '01/08/2021 12:47 PM',
      endedAt: '01/08/2021 12:47 PM',
      warnings: [],
      errors: ['Error message', 'Another error'],
      failures: [],
    },
    {
      label: 'Step with failures',
      job: 'csv_product_quick_export_another_one',
      status: 'Completed',
      summary: {},
      startedAt: '01/08/2021 12:47 PM',
      endedAt: '01/08/2021 12:47 PM',
      warnings: [],
      errors: [],
      failures: ['This is a failure', 'Failed again'],
    },
    {
      label: 'Step ok',
      job: 'csv_product_quickie_another_one',
      status: 'Completed',
      summary: {},
      startedAt: '01/08/2022 12:47 PM',
      endedAt: '01/08/2021 12:47 PM',
      warnings: [],
      errors: [],
      failures: [],
    },
  ],
};

test('It renders nothing if there is no step execution', () => {
  renderWithProviders(<SummaryTable jobExecution={{failures: []}} />);

  expect(screen.queryByText('pim_import_export.form.job_execution.summary.header.step')).not.toBeInTheDocument();
});

test('It renders the summary table of a job execution', () => {
  renderWithProviders(<SummaryTable jobExecution={jobExecution} />);

  expect(screen.getByText('pim_import_export.form.job_execution.summary.header.step')).toBeInTheDocument();
  expect(screen.getByText('This is a warning with one reason')).toBeInTheDocument();
  expect(screen.getByText('Error message')).toBeInTheDocument();
  expect(screen.getByText('This is a failure')).toBeInTheDocument();
  expect(screen.getByText('01/08/2022 12:47 PM')).toBeInTheDocument();
});

test('It can expand a warning when clicking on the display link', () => {
  renderWithProviders(<SummaryTable jobExecution={jobExecution} />);

  expect(screen.queryByText('another nice reason')).not.toBeInTheDocument();

  fireEvent.click(screen.getAllByText('job_execution.summary.display_item')[0]);

  expect(screen.getByText('another nice reason')).toBeInTheDocument();
});
