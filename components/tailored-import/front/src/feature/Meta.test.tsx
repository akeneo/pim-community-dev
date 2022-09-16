import React from 'react';
import {renderWithProviders} from 'feature/tests';
import {screen} from '@testing-library/react';
import {DataMapping} from './models';
import {Meta} from './Meta';

const columns = [
  {
    uuid: '5f942671-3ba3-4639-b96b-245a720ea587',
    index: 0,
    label: 'Column 1',
  },
  {
    uuid: '5f942671-3ba3-4639-b96b-245a720ea588',
    index: 1,
    label: 'Column 2',
  },
  {
    uuid: '5f942671-3ba3-4639-b96b-245a720ea589',
    index: 2,
    label: 'Column 3',
  },
  {
    uuid: '5f942671-3ba3-4639-b96b-245a720ea590',
    index: 3,
    label: 'Column 4',
  },
];
const data_mappings: DataMapping[] = [
  {
    uuid: '8175126a-5deb-426c-a829-c9b7949dc1f7',
    operations: [],
    sample_data: [],
    sources: ['5f942671-3ba3-4639-b96b-245a720ea590'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'sku',
      attribute_type: 'pim_catalog_identifier',
      action_if_empty: 'skip',
      locale: null,
      type: 'attribute',
      source_configuration: null,
    },
  },
  {
    uuid: '8175126a-5deb-426c-a829-c9b7949dc1f7',
    operations: [],
    sample_data: [],
    sources: ['5f942671-3ba3-4639-b96b-245a720ea587', '5f942671-3ba3-4639-b96b-245a720ea588'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'colors',
      attribute_type: 'pim_catalog_multiselect',
      action_if_empty: 'skip',
      locale: null,
      type: 'attribute',
      source_configuration: null,
    },
  },
  {
    uuid: 'd1249682-720e-11ec-90d6-0242ac120003',
    operations: [],
    sample_data: [],
    sources: ['5f942671-3ba3-4639-b96b-245a720ea587', '5f942671-3ba3-4639-b96b-245a720ea589'],
    target: {
      action_if_not_empty: 'set',
      channel: null,
      code: 'brands',
      attribute_type: 'pim_catalog_multiselect',
      action_if_empty: 'clear',
      locale: null,
      type: 'attribute',
      source_configuration: null,
    },
  },
];

test('it renders Meta component', () => {
  renderWithProviders(
    <Meta
      jobName={'xlsx_product_export'}
      connector={'Akeneo CSV Connector'}
      importStructure={{
        data_mappings,
        columns,
      }}
    />
  );

  expect(
    screen.getByText(
      `pim_import_export.form.job_instance.meta.job: batch_jobs.xlsx_product_export.label | pim_import_export.form.job_instance.meta.connector: Akeneo CSV Connector | akeneo.tailored_import.meta.mapped_columns`
    )
  ).toBeInTheDocument();
});
