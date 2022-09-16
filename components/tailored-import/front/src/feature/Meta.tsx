import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ImportStructure, countMappedColumnsInDataMappings} from './models';

type MetaProps = {
  jobName: string;
  connector: string;
  importStructure: ImportStructure;
};

const Meta = ({jobName, connector, importStructure}: MetaProps) => {
  const translate = useTranslate();

  const columnsCount = importStructure.columns.length;
  const mappedColumnsCount = countMappedColumnsInDataMappings(importStructure.data_mappings);

  const jobPart = `${translate('pim_import_export.form.job_instance.meta.job')}: ${translate(
    `batch_jobs.${jobName}.label`
  )}`;
  const connectorPart = `${translate('pim_import_export.form.job_instance.meta.connector')}: ${connector}`;
  const columnPart = translate(
    'akeneo.tailored_import.meta.mapped_columns',
    {count: mappedColumnsCount, total: columnsCount},
    mappedColumnsCount
  );

  return (
    <span>
      {jobPart} | {connectorPart} | {columnPart}
    </span>
  );
};
export type {MetaProps};
export {Meta};
