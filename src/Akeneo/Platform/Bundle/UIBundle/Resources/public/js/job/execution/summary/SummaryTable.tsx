import React from 'react';
import styled from 'styled-components';
import {Table, Badge, Helper, Level} from 'akeneo-design-system';
import {Translate, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {InnerTable} from './InnerTable';
import {WarningHelper} from './WarningHelper';
import {JobExecution, StepExecution} from '../models';

const SpacedTable = styled(Table)`
  margin-bottom: 40px;
`;

const SummaryCell = styled(Table.Cell)`
  padding: 0 10px;
  max-width: unset;
`;

const LargeCell = styled.td.attrs({colSpan: 5})`
  padding: 0 0 1px 0;
`;

const getStepKey = ({job, label}: StepExecution) => `batch_jobs.${job}.${label}.label`;

const getStepLabel = (translate: Translate, step: StepExecution): string => {
  let key = getStepKey(step);

  if (translate(key) === key) {
    key = `batch_jobs.default_steps.${step.label}`;
  }

  return translate(key);
};

const getStepStatusLevel = (step: StepExecution): Level => {
  if (0 < step.errors.length || 0 < step.failures.length) {
    return 'danger';
  }

  if (0 < step.warnings.length) {
    return 'warning';
  }

  return 'primary';
};

type SummaryTableProps = {
  jobExecution: JobExecution;
};

const SummaryTable = ({jobExecution}: SummaryTableProps) => {
  const translate = useTranslate();

  if (!jobExecution.stepExecutions) return null;

  return (
    <SpacedTable>
      <Table.Header sticky={0}>
        <Table.HeaderCell>{translate('pim_import_export.form.job_execution.summary.header.step')}</Table.HeaderCell>
        <Table.HeaderCell>{translate('pim_common.status')}</Table.HeaderCell>
        <Table.HeaderCell>{translate('pim_import_export.form.job_execution.summary.header.summary')}</Table.HeaderCell>
        <Table.HeaderCell>{translate('pim_import_export.form.job_execution.summary.header.start')}</Table.HeaderCell>
        <Table.HeaderCell>{translate('pim_import_export.form.job_execution.summary.header.end')}</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        {jobExecution.stepExecutions.map(step => (
          <React.Fragment key={getStepKey(step)}>
            <Table.Row>
              <Table.Cell rowTitle={true}>{getStepLabel(translate, step)}</Table.Cell>
              <Table.Cell>
                <Badge level={getStepStatusLevel(step)}>{step.status}</Badge>
              </Table.Cell>
              <SummaryCell>
                <InnerTable content={step.summary} />
              </SummaryCell>
              <Table.Cell>{step.startedAt}</Table.Cell>
              <Table.Cell>{step.endedAt}</Table.Cell>
            </Table.Row>
            {step.errors.map((error, index) => (
              <Table.Row key={index}>
                <LargeCell>
                  <Helper level="error">{error}</Helper>
                </LargeCell>
              </Table.Row>
            ))}
            {step.warnings.map((warning, index) => (
              <Table.Row key={index}>
                <LargeCell>
                  <WarningHelper key={index} warning={warning} />
                </LargeCell>
              </Table.Row>
            ))}
            {[...step.failures, ...jobExecution.failures].map((failure, index) => (
              <Table.Row key={index}>
                <LargeCell>
                  <Helper level="error">{'string' === typeof failure ? failure : failure.label}</Helper>
                </LargeCell>
              </Table.Row>
            ))}
          </React.Fragment>
        ))}
      </Table.Body>
    </SpacedTable>
  );
};

export {SummaryTable};
