import React from 'react';
import styled from 'styled-components';
import {ProgressBar, Table} from 'akeneo-design-system';
import {
  JobExecutionRow,
  getStepExecutionRowTrackingLevel,
  getStepExecutionRowTrackingPercent,
  getStepExecutionRowTrackingProgressLabel,
} from '../models';
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
  const currentStep = jobExecutionRow.tracking.steps[jobExecutionRow.tracking.current_step - 1];

  return (
    <Table.Cell title={getStepExecutionRowTrackingProgressLabel(translate, jobExecutionRow.status, currentStep)}>
      <Container>
        {'STARTING' === jobExecutionRow.status ? (
          <ProgressBar percent="indeterminate" level="primary" />
        ) : (
          [...Array(jobExecutionRow.tracking.total_step)].map((_, stepIndex) => {
            const step = jobExecutionRow.tracking.steps[stepIndex];

            return undefined !== step ? (
              <ProgressBar
                key={stepIndex}
                level={getStepExecutionRowTrackingLevel(step)}
                percent={getStepExecutionRowTrackingPercent(step)}
              />
            ) : (
              <ProgressBar key={stepIndex} level="primary" percent={0} />
            );
          })
        )}
      </Container>
    </Table.Cell>
  );
};

export {ProgressCell};
