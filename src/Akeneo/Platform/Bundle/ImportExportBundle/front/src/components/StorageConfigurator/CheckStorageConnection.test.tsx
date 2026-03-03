import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CheckStorageConnection} from './CheckStorageConnection';
import {AmazonS3Storage} from '../../models';

const storage: AmazonS3Storage = {
  type: 'amazon_s3',
  file_path: '/tmp/file.xlsx',
  region: 'eu-west-1',
  bucket: 'my_bucket',
  key: 'a_key',
  secret: 'my_s3cr3t',
};

let hookResponse = [true, false, jest.fn()];

jest.mock('../../hooks/useCheckStorageConnection', () => ({
  useCheckStorageConnection: () => hookResponse,
}));

test('it disables check button when the hook is fetching', () => {
  renderWithProviders(<CheckStorageConnection jobInstanceCode="csv_product_export" storage={storage} />);

  expect(screen.getByText('pim_import_export.form.job_instance.connection_checker.label')).toBeDisabled();
});

test('it displays a helper when connection is invalid', () => {
  hookResponse = [false, true, jest.fn()];

  renderWithProviders(<CheckStorageConnection jobInstanceCode="csv_product_export" storage={storage} />);

  expect(screen.getByText('pim_import_export.form.job_instance.connection_checker.exception')).toBeInTheDocument();
});
