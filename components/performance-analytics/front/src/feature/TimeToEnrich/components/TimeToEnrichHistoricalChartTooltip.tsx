import React from 'react';
import styled from 'styled-components';

const Container = styled('div')`
  color: #868c97;
  margin: 0;
  padding: 0;
  font-size: 10px;
  height: 100%;
  width: 100%;
`;

const DataContainer = styled('div')`
  border: 1px solid #868c97;
  border-radius: 5px;
  background-color: #ffffff;
  padding: 1em 0.5em 0.5em 1em;
`;

const Score = styled('div')`
  padding-bottom: 0.2em;
`;

type TooltipProps = {
  x: number;
  y: number;
};

const TimeToEnrichHistoricalChartTooltip: React.FC<TooltipProps> = props => {
  return (
    <g style={{pointerEvents: 'none'}}>
      <foreignObject x={props.x} y={props.y} width="90" height="50">
        <Container>
          <DataContainer>
            <Score>TODO, need to be implemented.</Score>
          </DataContainer>
        </Container>
      </foreignObject>
    </g>
  );
};

export {TimeToEnrichHistoricalChartTooltip};
