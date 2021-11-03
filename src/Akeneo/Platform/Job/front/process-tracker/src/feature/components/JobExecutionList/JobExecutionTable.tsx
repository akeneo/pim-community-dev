import React from 'react';
import { useTranslate } from '@akeneo-pim-community/shared';
import { Table } from 'akeneo-design-system';
import { JobExecutionRow } from '../../models/JobExecutionTable';

const JobExecutionTable = ({jobExecutionRows}: {jobExecutionRows: JobExecutionRow[]}) => {
  const translate = useTranslate();

  return (
    <Table>
      <Table.Header sticky={0}>
        <Table.HeaderCell>
          {translate('akeneo_job_process_tracker.job_execution_list.table.headers.job_name')}
        </Table.HeaderCell>
        <Table.HeaderCell>
          {translate('akeneo_job_process_tracker.job_execution_list.table.headers.type')}
        </Table.HeaderCell>
        <Table.HeaderCell>
          {translate('akeneo_job_process_tracker.job_execution_list.table.headers.started_at')}
        </Table.HeaderCell>
        <Table.HeaderCell>
          {translate('akeneo_job_process_tracker.job_execution_list.table.headers.username')}
        </Table.HeaderCell>
        <Table.HeaderCell>
          {translate('akeneo_job_process_tracker.job_execution_list.table.headers.status')}
        </Table.HeaderCell>
        <Table.HeaderCell>
          {translate('akeneo_job_process_tracker.job_execution_list.table.headers.warnings')}
        </Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        {jobExecutionRows.map((jobExecutionRow: JobExecutionRow) => (
          <Table.Row key={jobExecutionRow.job_execution_id}>
            <Table.Cell rowTitle={true}>
              {jobExecutionRow.job_name}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.type}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.start_at}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.username}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.status}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.warning_count}
            </Table.Cell>
          </Table.Row>
        ))}
      </Table.Body>
    </Table>
  );
}

export {JobExecutionTable};
