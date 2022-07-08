import React, {FC, SyntheticEvent} from 'react';
import styled, {css} from 'styled-components';
import {QualityScoreValue} from '../../domain';
import {QualityScore} from './QualityScore';

type Props = {
  score: QualityScoreValue;
  onClick?: (event: SyntheticEvent) => void;
  stacked?: boolean;
};

const QualityScoreBar: FC<Props> = ({score, onClick, stacked = false}) => {
  const handleAction = (event: SyntheticEvent) => {
    if (undefined === onClick) return;

    onClick(event);
  };

  return (
    <Container score={score} onClick={handleAction} data-testid="quality-score-bar">
      <QualityScore
        key={`ranking-score-A`}
        score={'A'}
        size={'A' === score ? 'big' : 'normal'}
        rounded={'left'}
        stacked={stacked && 'A' === score}
      />
      <QualityScore
        key={`ranking-score-B`}
        score={'B'}
        size={'B' === score ? 'big' : 'normal'}
        rounded={'none'}
        stacked={stacked && 'B' === score}
      />
      <QualityScore
        key={`ranking-score-C`}
        score={'C'}
        size={'C' === score ? 'big' : 'normal'}
        rounded={'none'}
        stacked={stacked && 'C' === score}
      />
      <QualityScore
        key={`ranking-score-D`}
        score={'D'}
        size={'D' === score ? 'big' : 'normal'}
        stacked={stacked && 'D' === score}
        rounded={'none'}
      />
      <QualityScore
        key={`ranking-score-E`}
        score={'E'}
        size={'E' === score ? 'big' : 'normal'}
        stacked={stacked && 'E' === score}
        rounded={'right'}
      />
    </Container>
  );
};

const Container = styled.div<{score: string | null}>`
  display: flex;
  position: relative;
  top: 1px;
  padding-top: 2px;
  height: 25px;
  cursor: pointer;

  ${props => props.score === null && NoScoreStyle}
`;

const NoScoreStyle = css`
  opacity: 0.3;
`;

export {QualityScoreBar};
