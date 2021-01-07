import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {ShowProfile} from '../../../../../Resources/public/js/job/execution/ShowProfile';
import {JobInstance} from '../../../../../Resources/public/js/job/execution/model/job-execution';

const importJobInstance: JobInstance = {
  code: 'csv_attribute_import',
  label: 'Demo CSV attribute import',
  type: 'import',
};

const exportJobInstance: JobInstance = {
  code: 'csv_attribute_export',
  label: 'Demo CSV attribute export',
  type: 'export',
};

const quickExportJobInstance: JobInstance = {
  code: 'csv_product_quick_export',
  label: 'CSV product quick export',
  type: 'quick_export',
};

test('It renders the show profile link correctly if the job is import', () => {
  renderWithProviders(<ShowProfile jobInstance={importJobInstance} />);

  const link = screen.getByText('pim_import_export.form.job_execution.button.show_profile.title');
  expect(link).toBeInTheDocument();
  expect(link).toHaveAttribute('href', '#pim_importexport_import_profile_show');
});

test('It renders the show profile link correctly if the job is export', () => {
  renderWithProviders(<ShowProfile jobInstance={exportJobInstance} />);

  const link = screen.getByText('pim_import_export.form.job_execution.button.show_profile.title');
  expect(link).toBeInTheDocument();
  expect(link).toHaveAttribute('href', '#pim_importexport_export_profile_show');
});

test('It does not render anything if the job is not import or export', () => {
  renderWithProviders(<ShowProfile jobInstance={quickExportJobInstance} />);

  expect(screen.queryByText('pim_import_export.form.job_execution.button.show_profile.title')).not.toBeInTheDocument();
});
