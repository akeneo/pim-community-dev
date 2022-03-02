import React, {FC, SyntheticEvent} from 'react';
import styled, {css} from 'styled-components';
import {QualityScore} from './QualityScore';

type Props = {
  currentScore: string | null;
  onClick?: (event: SyntheticEvent) => void;
  stacked?: boolean;
};

const QualityScoreBar: FC<Props> = ({currentScore, onClick, stacked = false}) => {
  const handleAction = (event: SyntheticEvent) => {
    if (undefined === onClick) return;

    onClick(event);
  };

  return (
    <Container currentScore={currentScore} onClick={handleAction} data-testid="quality-score-bar">
      <QualityScore
        key={`ranking-score-A`}
        score={'A'}
        size={'A' === currentScore ? 'big' : 'normal'}
        rounded={'left'}
        stacked={stacked && 'A' === currentScore}
      />
      <QualityScore
        key={`ranking-score-B`}
        score={'B'}
        size={'B' === currentScore ? 'big' : 'normal'}
        rounded={'none'}
        stacked={stacked && 'B' === currentScore}
      />
      <QualityScore
        key={`ranking-score-C`}
        score={'C'}
        size={'C' === currentScore ? 'big' : 'normal'}
        rounded={'none'}
        stacked={stacked && 'C' === currentScore}
      />
      <QualityScore
        key={`ranking-score-D`}
        score={'D'}
        size={'D' === currentScore ? 'big' : 'normal'}
        stacked={stacked && 'D' === currentScore}
        rounded={'none'}
      />
      <QualityScore
        key={`ranking-score-E`}
        score={'E'}
        size={'E' === currentScore ? 'big' : 'normal'}
        stacked={stacked && 'E' === currentScore}
        rounded={'right'}
      />
    </Container>
  );
};

const Container = styled.div<{currentScore: string | null}>`
  display: flex;
  position: relative;
  top: 1px;
  padding-top: 2px;
  height: 25px;
  cursor: pointer;

  ${props => props.currentScore === null && NoScoreStyle}
`;

const NoScoreStyle = css`
  opacity: 0.3;
`;

export {QualityScoreBar};
