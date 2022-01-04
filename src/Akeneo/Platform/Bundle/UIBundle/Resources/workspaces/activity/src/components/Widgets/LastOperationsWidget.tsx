import React from 'react';
import {Badge, Button, SectionTitle, SettingsIllustration, Table} from 'akeneo-design-system';
import {NoDataSection, NoDataText, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useDashboardLastOperations} from '../../hooks';
import {Operation} from '../../domain';
import styled from 'styled-components';

const JOB_STARTING = '2';
const JOB_STARTED = '3';
const JOB_FAILED = '6';

const LastOperationsWidget = () => {
  const translate = useTranslate();
  const router = useRouter();
  const data: Operation[] | null = useDashboardLastOperations();

  const redirectToJob = (jobId: string) => router.redirectToRoute('akeneo_job_process_tracker_details', {id: jobId});

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_dashboard.widget.last_operations.title')}</SectionTitle.Title>
        {data !== null && data.length > 0 && (
          <>
            <SectionTitle.Spacer />
            <Button
              ghost={true}
              size="small"
              level="tertiary"
              title="Show job tracker"
              onClick={() => router.redirectToRoute('akeneo_job_process_tracker_index')}
            >
              {translate('pim_import_export.widget.last_operations.header.view_all')}
            </Button>
          </>
        )}
      </SectionTitle>

      {data === null ||
        (data.length === 0 && (
          <NoDataSection style={{marginTop: 0}}>
            <SettingsIllustration size={128} />
            <NoDataText style={{fontSize: '15px'}}>
              {translate('pim_import_export.widget.last_operations.empty')}
            </NoDataText>
          </NoDataSection>
        ))}

      {data !== null && data.length > 0 && (
        <Table>
          <Table.Header>
            <Table.HeaderCell>
              {translate('akeneo_job_process_tracker.job_execution_list.table.headers.started_at')}
            </Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.type')}</Table.HeaderCell>
            <Table.HeaderCell>
              {translate('akeneo_job_process_tracker.job_execution_list.table.headers.job_name')}
            </Table.HeaderCell>
            <Table.HeaderCell>
              {translate('akeneo_job_process_tracker.job_execution_list.table.headers.username')}
            </Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.status')}</Table.HeaderCell>
            <Table.HeaderCell>
              {translate('akeneo_job_process_tracker.job_execution_list.table.headers.warning_count')}
            </Table.HeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {data.map((operation: Operation) => {
              const badgeLevel =
                operation.status === JOB_FAILED ? 'danger' : operation.tracking.warning ? 'warning' : 'primary';
              const counter = operation.status === JOB_FAILED ? 1 : operation.warningCount;

              return (
                <Table.Row key={`operation${operation.id}`}>
                  <Table.Cell>{operation.date}</Table.Cell>
                  <Table.Cell>{translate(`akeneo_job_process_tracker.type_filter.${operation.type}`)}</Table.Cell>
                  <Table.Cell>{operation.label}</Table.Cell>
                  <Table.Cell>{operation.username}</Table.Cell>
                  <Table.Cell>
                    <Badge level={badgeLevel}>
                      {translate(operation.statusLabel)}
                      {(operation.status === JOB_STARTING || operation.status === JOB_STARTED) &&
                        ` ${operation.tracking.currentStep}/${operation.tracking.totalSteps}`}
                    </Badge>
                  </Table.Cell>
                  <Table.Cell>{counter > 0 ? counter : '-'}</Table.Cell>
                  <TableActionCell>
                    {operation.canSeeReport && (
                      <Button ghost={true} size="small" level="tertiary" onClick={() => redirectToJob(operation.id)}>
                        {translate('pim_import_export.widget.last_operations.details')}
                      </Button>
                    )}
                  </TableActionCell>
                </Table.Row>
              );
            })}
          </Table.Body>
        </Table>
      )}
    </>
  );
};

const TableActionCell = styled(Table.Cell)`
  width: 50px;
`;

export {LastOperationsWidget};
