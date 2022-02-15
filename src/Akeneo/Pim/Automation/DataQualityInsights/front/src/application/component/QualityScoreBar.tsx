import React, {FC} from 'react';
import styled, {css} from 'styled-components';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../listener';
import {QualityScore} from './QualityScore';

type Props = {
  currentScore: string | null;
  stacked?: boolean;
};

const QualityScoreBar: FC<Props> = ({currentScore, stacked = false}) => {
  return (
    <Container
      currentScore={currentScore}
      onClick={() => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB))}
    >
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
  border-right: 1px ${({theme}) => theme.color.grey80} solid;
  padding-right: 20px;
  margin-right: 20px;
  padding-top: 2px;
  height: 25px;
  cursor: pointer;

  ${props => props.currentScore === null && NoScoreStyle}
`;

const NoScoreStyle = css`
  opacity: 0.3;
`;

export {QualityScoreBar};
