import React from 'react';
import {Button, Placeholder, SectionTitle, SettingsIllustration, Table} from 'akeneo-design-system';
import {useDateFormatter, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {getDefaultJobExecutionFilter, JobExecutionFilter} from '../models';
import {JobExecutionStatus} from '../components/common';
import {useJobExecutionTable} from '../hooks';

const lastOperationsWidgetFilter: JobExecutionFilter = {
  ...getDefaultJobExecutionFilter(),
  size: 10,
};

const COLUMN_HEADERS = ['started_at', 'type', 'job_name', 'username', 'status', 'warning_count'];

const LastOperationsWidget = () => {
  const translate = useTranslate();
  const dateFormatter = useDateFormatter();
  const router = useRouter();
  const [jobExecutionTable] = useJobExecutionTable(lastOperationsWidgetFilter, false);

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo_job_process_tracker.last_operations.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <Button href={`#${router.generate('akeneo_job_process_tracker_index')}`}>
          {translate('akeneo_job_process_tracker.last_operations.view_all')}
        </Button>
      </SectionTitle>
      {0 === jobExecutionTable?.rows?.length ? (
        <Placeholder
          title={translate('akeneo_job_process_tracker.last_operations.no_result')}
          illustration={<SettingsIllustration />}
        />
      ) : (
        <Table>
          <Table.Header>
            {COLUMN_HEADERS.map(columnHeader => (
              <Table.HeaderCell key={columnHeader}>
                {translate(`akeneo_job_process_tracker.job_execution_list.table.headers.${columnHeader}`)}
              </Table.HeaderCell>
            ))}
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {jobExecutionTable?.rows?.map(jobExecutionRow => (
              <Table.Row key={jobExecutionRow.job_execution_id}>
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
                <Table.Cell>{translate(`akeneo_job_process_tracker.type_filter.${jobExecutionRow.type}`)}</Table.Cell>
                <Table.Cell>{jobExecutionRow.job_name}</Table.Cell>
                <Table.Cell>{jobExecutionRow.username}</Table.Cell>
                <Table.Cell>
                  <JobExecutionStatus
                    showTooltip={false}
                    status={jobExecutionRow.status}
                    hasWarning={0 < jobExecutionRow.warning_count}
                    hasError={jobExecutionRow.has_error}
                    currentStep={jobExecutionRow.tracking.current_step}
                    totalSteps={jobExecutionRow.tracking.total_step}
                  />
                </Table.Cell>
                <Table.Cell>{0 < jobExecutionRow.warning_count ? jobExecutionRow.warning_count : '-'}</Table.Cell>
                <Table.Cell>
                  <Button
                    href={`#${router.generate('akeneo_job_process_tracker_details', {
                      id: jobExecutionRow.job_execution_id,
                    })}`}
                    ghost={true}
                    level="tertiary"
                    size="small"
                  >
                    {translate('akeneo_job_process_tracker.last_operations.details')}
                  </Button>
                </Table.Cell>
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
      )}
    </>
  );
};

export {LastOperationsWidget};
