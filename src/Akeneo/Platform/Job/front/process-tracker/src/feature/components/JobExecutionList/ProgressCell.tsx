import React from 'react';
import styled from 'styled-components';
import {ProgressBar, Table} from 'akeneo-design-system';
import {JobExecutionRow} from '../../models';
import {
  getStepExecutionRowTrackingLevel,
  getStepExecutionRowTrackingPercent,
} from '../../models/StepExecutionRowTracking';

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
  return (
    <Table.Cell>
      <Container>
        {jobExecutionRow.tracking.steps.map(step => (
          <ProgressBar
            level={getStepExecutionRowTrackingLevel(step)}
            percent={getStepExecutionRowTrackingPercent(step)}
          />
        ))}
      </Container>
    </Table.Cell>
  );
};

export {ProgressCell};
