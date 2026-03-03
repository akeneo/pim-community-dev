import React, {MouseEvent} from 'react';
import {Table} from 'akeneo-design-system';
import {useDateFormatter, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {JobExecutionRow, jobCanBeStopped, canShowJobExecutionDetail} from '../models';
import {JobExecutionStatus, ProgressCell, StopJobAction} from './common';

const COLUMN_HEADERS = ['started_at', 'username', 'progress', 'status', 'warning_count'];

type LastExecutionsTableProps = {
  sticky?: number;
  jobExecutionRows: JobExecutionRow[];
  onTableRefresh: () => void;
};

const LastExecutionTable = ({sticky, jobExecutionRows, onTableRefresh}: LastExecutionsTableProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const dateFormatter = useDateFormatter();
  const router = useRouter();

  const handleRowClick = (jobExecutionId: number) => (event: MouseEvent<HTMLTableRowElement>) => {
    const route = router.generate('akeneo_job_process_tracker_details', {id: jobExecutionId});

    if (event.metaKey || event.ctrlKey) {
      const newTab = window.open(`#${route}`, '_blank');
      newTab?.focus();

      return;
    }

    router.redirect(route);
  };

  return (
    <Table>
      <Table.Header sticky={sticky}>
        {COLUMN_HEADERS.map(sortableColumnHeader => (
          <Table.HeaderCell key={sortableColumnHeader}>
            {translate(`akeneo_job_process_tracker.job_execution_list.table.headers.${sortableColumnHeader}`)}
          </Table.HeaderCell>
        ))}
        <Table.HeaderCell />
      </Table.Header>
      <Table.Body>
        {jobExecutionRows.map(jobExecutionRow => (
          <Table.Row
            key={jobExecutionRow.job_execution_id}
            onClick={
              canShowJobExecutionDetail(isGranted, jobExecutionRow)
                ? event => handleRowClick(jobExecutionRow.job_execution_id)(event)
                : undefined
            }
          >
            <Table.Cell>
              {jobExecutionRow.started_at
                ? dateFormatter(jobExecutionRow.started_at, {
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                  })
                : '-'}
            </Table.Cell>
            <Table.Cell>{jobExecutionRow.username}</Table.Cell>
            <ProgressCell jobExecutionRow={jobExecutionRow} />
            <Table.Cell>
              <JobExecutionStatus
                status={jobExecutionRow.status}
                hasWarning={jobExecutionRow.warning_count > 0}
                hasError={jobExecutionRow.has_error}
                currentStep={jobExecutionRow.tracking.current_step}
                totalSteps={jobExecutionRow.tracking.total_step}
              />
            </Table.Cell>
            <Table.Cell>{jobExecutionRow.warning_count > 0 ? jobExecutionRow.warning_count : '-'}</Table.Cell>
            <Table.ActionCell>
              <StopJobAction
                id={jobExecutionRow.job_execution_id.toString()}
                jobLabel={jobExecutionRow.job_name}
                isStoppable={jobCanBeStopped(jobExecutionRow)}
                onStop={onTableRefresh}
                ghost={true}
                size="small"
              />
            </Table.ActionCell>
          </Table.Row>
        ))}
      </Table.Body>
    </Table>
  );
};

export {LastExecutionTable};
