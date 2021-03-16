import React from 'react';
import {Badge, Button, SectionTitle, SettingsIllustration, Table} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {NoDataSection, NoDataText} from '@akeneo-pim-community/shared';
import {useDashboardLastOperations} from '../../hooks';
import {Operation} from '../../domain';

const JOB_STARTING = '2';
const JOB_STARTED = '3';
const JOB_FAILED = '6';

const LastOperationsWidget = () => {
  const translate = useTranslate();
  const router = useRouter();
  const data: Operation[] | null = useDashboardLastOperations();

  const redirectToJob = (jobId: string) => {
    // @ts-ignore
    router.redirectToRoute('pim_enrich_job_tracker_show', {id: jobId});
  };

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_dashboard.widget.last_operations.title')}</SectionTitle.Title>
        {data !== null && data.length > 0 && (
          <>
            <SectionTitle.Spacer />
            <Button
              ghost
              size={'small'}
              level={'tertiary'}
              title="Show job tracker"
              // @ts-ignore
              onClick={() => router.redirectToRoute('pim_enrich_job_tracker_index')}
            >
              {translate('pim_import_export.widget.last_operations.header.view_all')}
            </Button>
          </>
        )}
      </SectionTitle>

      {data === null ||
        (data.length === 0 && (
          <NoDataSection style={{marginTop: 0}}>
            <SettingsIllustration width={128} height={128} />
            <NoDataText>{translate('pim_import_export.widget.last_operations.empty')}</NoDataText>
          </NoDataSection>
        ))}

      {data !== null && data.length > 0 && (
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_import_export.widget.last_operations.date')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.type')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_import_export.widget.last_operations.profile_name')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_import_export.widget.last_operations.username')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_common.status')}</Table.HeaderCell>
            <Table.HeaderCell>{translate('pim_import_export.widget.last_operations.warning_count')}</Table.HeaderCell>
            <Table.HeaderCell />
          </Table.Header>
          <Table.Body>
            {data.map((operation: Operation) => {
              const badgeLevel =
                operation.status === JOB_FAILED
                  ? 'danger'
                  : operation.warningCount !== null && operation.warningCount !== '0'
                  ? 'warning'
                  : 'primary';
              const counter = operation.status === JOB_FAILED ? 1 : operation.warningCount;

              return (
                <Table.Row key={`operation${operation.id}`}>
                  <Table.Cell>{operation.date}</Table.Cell>
                  <Table.Cell>
                    {translate(`pim_import_export.widget.last_operations.job_type.${operation.type}`)}
                  </Table.Cell>
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
                  <Table.Cell>
                    {operation.canSeeReport && (
                      <Button
                        type="button"
                        ghost
                        size="small"
                        level="tertiary"
                        onClick={() => redirectToJob(operation.id)}
                      >
                        {translate('pim_import_export.widget.last_operations.details')}
                      </Button>
                    )}
                  </Table.Cell>
                </Table.Row>
              );
            })}
          </Table.Body>
        </Table>
      )}
    </>
  );
};

export {LastOperationsWidget};
