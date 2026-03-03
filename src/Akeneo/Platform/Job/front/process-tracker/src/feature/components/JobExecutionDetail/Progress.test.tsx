import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Progress} from './Progress';

const mockTranslate = jest.fn((key: string, parameters: any, count: number) => {
  return key;
});

jest.mock('@akeneo-pim-community/shared/lib/hooks/useTranslate', () => ({
  useTranslate: () => mockTranslate,
}));

test('it shows the progress of a job', () => {
  renderWithProviders(
    <Progress
      jobStatus="FAILED"
      steps={[
        {
          jobName: 'csv_product_export',
          stepName: 'export',
          status: 'IN_PROGRESS',
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
          status: 'IN_PROGRESS',
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
          status: 'IN_PROGRESS',
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
          status: 'IN_PROGRESS',
          isTrackable: true,
          hasWarning: false,
          hasError: false,
          duration: 10,
          processedItems: 30,
          totalItems: 60,
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
          status: 'PAUSED',
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
          duration: 10,
          processedItems: 30,
          totalItems: 300,
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

  expect(mockTranslate).toHaveBeenCalledWith('duration.hours', {count: '23'}, 23);
  expect(mockTranslate).toHaveBeenCalledWith('duration.minutes', {count: '59'}, 59);
  expect(mockTranslate).toHaveBeenCalledWith('pim_import_export.tracking.in_progress', {duration: ' '});
  expect(mockTranslate).toHaveBeenCalledWith('batch_jobs.csv_product_export.export.label');
  expect(mockTranslate).toHaveBeenCalledWith('pim_import_export.tracking.untrackable');
});
