import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {Progress} from './Progress';

test('it shows the progress of a job', () => {
  renderWithProviders(
    <Progress
      jobStatus="FAILED"
      steps={[
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'STARTED',
          isTrackable: false,
          hasWarning: false,
          hasError: false,
          duration: 14,
          processedItems: 30,
          totalItems: 135,
        },
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'STARTED',
          isTrackable: true,
          hasWarning: false,
          hasError: false,
          duration: 14,
          processedItems: 30,
          totalItems: 0,
        },
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'STARTED',
          isTrackable: true,
          hasWarning: false,
          hasError: false,
          duration: 14,
          processedItems: 30,
          totalItems: 1,
        },
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'STARTING',
          isTrackable: true,
          hasWarning: false,
          hasError: false,
          duration: 14,
          processedItems: 30,
          totalItems: 135,
        },
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'ABANDONED',
          isTrackable: true,
          hasWarning: false,
          hasError: false,
          duration: 14,
          processedItems: 30,
          totalItems: 135,
        },
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'COMPLETED',
          isTrackable: true,
          hasWarning: true,
          hasError: false,
          duration: 14,
          processedItems: 30,
          totalItems: 135,
        },
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'FAILED',
          isTrackable: true,
          hasWarning: false,
          hasError: true,
          duration: 14,
          processedItems: 30,
          totalItems: 0,
        },
      ]}
    />
  );

  expect(screen.getByText('pim_import_export.tracking.not_started')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.tracking.in_progress')).toBeInTheDocument();
  expect(screen.getByText('pim_import_export.tracking.estimating')).toBeInTheDocument();
});
