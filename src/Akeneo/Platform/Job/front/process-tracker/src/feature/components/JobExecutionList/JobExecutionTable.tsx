import React from 'react';
import { useDateFormatter, useTranslate } from '@akeneo-pim-community/shared';
import { Table } from 'akeneo-design-system';
import { JobExecutionRow } from '../../models/JobExecutionTable';
import JobExecutionStatus from "../JobExecutionStatus";

const JobExecutionTable = ({jobExecutionRows}: {jobExecutionRows: JobExecutionRow[]}) => {
  const translate = useTranslate();
  const dateFormatter = useDateFormatter();

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
              {translate(`pim_import_export.widget.last_operations.job_type.${jobExecutionRow.type}`)}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.started_at && dateFormatter(jobExecutionRow.started_at, {day: '2-digit', hour: '2-digit', minute: '2-digit', month: '2-digit', year: 'numeric'})}
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.username}
            </Table.Cell>
            <Table.Cell>
              <JobExecutionStatus
                status={jobExecutionRow.status}
                hasWarning={jobExecutionRow.warning_count > 0}
                hasError={jobExecutionRow.error_count > 0}
                currentStep={jobExecutionRow.tracking.current_step}
                totalSteps={jobExecutionRow.tracking.total_step}
              />
            </Table.Cell>
            <Table.Cell>
              {jobExecutionRow.warning_count > 0 ? jobExecutionRow.warning_count : '-'}
            </Table.Cell>
          </Table.Row>
        ))}
      </Table.Body>
    </Table>
  );
}

export {JobExecutionTable};
