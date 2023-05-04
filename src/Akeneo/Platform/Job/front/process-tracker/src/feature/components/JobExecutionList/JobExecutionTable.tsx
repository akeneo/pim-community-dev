import React, {useCallback, MouseEvent} from 'react';
import {Table} from 'akeneo-design-system';
import {useDateFormatter, useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {JobExecutionRow, JobExecutionFilterSort, jobCanBeStopped, canShowJobExecutionDetail} from '../../models';
import {JobExecutionStatus, ProgressCell, StopJobAction} from '../common';

const COLUMN_HEADERS = [
  {name: 'job_name', isSortable: true},
  {name: 'type', isSortable: true},
  {name: 'started_at', isSortable: true},
  {name: 'username', isSortable: true},
  {name: 'progress', isSortable: false},
  {name: 'status', isSortable: true},
  {name: 'warning_count', isSortable: false},
];

type JobExecutionTableProps = {
  sticky?: number;
  jobExecutionRows: JobExecutionRow[];
  onSortChange: (sort: JobExecutionFilterSort) => void;
  onTableRefresh: () => void;
  currentSort: JobExecutionFilterSort;
};

const JobExecutionTable = ({
  sticky,
  jobExecutionRows,
  onSortChange,
  currentSort,
  onTableRefresh,
}: JobExecutionTableProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const dateFormatter = useDateFormatter();
  const sortDirection = 'ASC' === currentSort.direction ? 'ascending' : 'descending';
  const router = useRouter();

  const handleRowClick = useCallback(
    (jobExecutionId: number) => (event: MouseEvent<HTMLTableRowElement>) => {
      const route = router.generate('akeneo_job_process_tracker_details', {id: jobExecutionId});

      if (event.metaKey || event.ctrlKey) {
        const newTab = window.open(`#${route}`, '_blank');
        newTab?.focus();

        return;
      }

      router.redirect(route);
    },
    [router]
  );

  return (
    <Table>
      <Table.Header sticky={sticky}>
        {COLUMN_HEADERS.map(({name, isSortable}) => (
          <Table.HeaderCell
            key={name}
            isSortable={isSortable}
            onDirectionChange={
              isSortable
                ? direction => {
                    if ('none' !== direction) {
                      onSortChange({column: name, direction: 'ascending' === direction ? 'ASC' : 'DESC'});
                    }
                  }
                : undefined
            }
            sortDirection={isSortable ? (currentSort.column === name ? sortDirection : 'none') : undefined}
          >
            {translate(`akeneo_job_process_tracker.job_execution_list.table.headers.${name}`)}
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
            <Table.Cell rowTitle={true}>{jobExecutionRow.job_name}</Table.Cell>
            <Table.Cell>{translate(`akeneo_job_process_tracker.type_filter.${jobExecutionRow.type}`)}</Table.Cell>
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
                showTooltip={true}
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

export {JobExecutionTable};
