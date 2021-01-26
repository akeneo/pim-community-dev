import * as React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {Status} from '../../../../Resources/public/js/job/execution/Status';
import {JobExecutionTracking} from '../../../../Resources/public/js/job/execution/model/job-execution';

test('it render a job execution status with the correct label', () => {
  const tracking: JobExecutionTracking = {
    status: 'COMPLETED',
    currentStep: 1,
    totalSteps: 1,
    steps: [
      {
        jobName: 'csv_product_export',
        stepName: 'export',
        duration: 1,
        hasWarning: false,
        hasError: false,
        isTrackable: true,
        processedItems: 10,
        totalItems: 100,
        status: 'COMPLETED',
      },
    ],
    error: false,
    warning: false,
  };

  renderWithProviders(<Status tracking={tracking} />);

  expect(screen.getByText('pim_common.status')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.job_status.COMPLETED')).toBeInTheDocument();
});
