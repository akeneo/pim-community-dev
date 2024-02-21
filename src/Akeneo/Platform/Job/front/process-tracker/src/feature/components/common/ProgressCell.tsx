import React from 'react';
import styled from 'styled-components';
import {ProgressBar, Table} from 'akeneo-design-system';
import {
  JobExecutionRow,
  getStepExecutionRowTrackingLevel,
  getStepExecutionRowTrackingPercent,
  getJobExecutionRowTrackingProgressLabel,
} from '../../models';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  display: grid;
  grid-auto-flow: column;
  grid-gap: 4px;
  grid-auto-columns: 1fr;
  flex: 1;
  min-width: 100px;
  max-width: 200px;
`;

type ProgressCellProps = {
  jobExecutionRow: JobExecutionRow;
};

const ProgressCell = ({jobExecutionRow}: ProgressCellProps) => {
  const translate = useTranslate();

  return (
    <Table.Cell title={getJobExecutionRowTrackingProgressLabel(translate, jobExecutionRow)}>
      <Container>
        {[...Array(jobExecutionRow.tracking.total_step)].map((_, stepIndex) => {
          if ('STARTING' === jobExecutionRow.status) {
            return <ProgressBar key={stepIndex} percent="indeterminate" level="primary" />;
          }

          const step = jobExecutionRow.tracking.steps[stepIndex] ?? null;

          return null === step ? (
            <ProgressBar key={stepIndex} level="primary" percent={0} />
          ) : (
            <ProgressBar
              key={stepIndex}
              level={getStepExecutionRowTrackingLevel(step)}
              percent={getStepExecutionRowTrackingPercent(step)}
            />
          );
        })}
      </Container>
    </Table.Cell>
  );
};

export {ProgressCell};
