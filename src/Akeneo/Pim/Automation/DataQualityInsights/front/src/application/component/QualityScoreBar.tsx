import React, {FC} from 'react';
import {QualityScore} from './QualityScore';
import styled, {css} from 'styled-components';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../listener';

type Props = {
  currentScore: string | null;
  appearance: 'regular' | 'stacked';
};

const QualityScoreBar: FC<Props> = props => {
  const {
    currentScore,
    // for testing PLG-747, should be 'regular' by default
    // TODO change this in last commit
    appearance = 'stacked',
  } = props;
  return (
    <Container
      currentScore={currentScore}
      onClick={() => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB))}
    >
      {['A', 'B', 'C', 'D', 'E'].map((score: string) => (
        <QualityScore
          key={`ranking-score-${score}`}
          isSelected={score === currentScore}
          score={score}
          appearance={appearance}
        />
      ))}
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

  > :first-child {
    border-radius: 4px 0 0 4px;
  }
  > :last-child {
    border-radius: 0 4px 4px 0;
  }

  > :not(:first-child):not(:last-child) {
    border-radius: 0;
  }

  ${props => props.currentScore === null && NoScoreStyle}
`;

const NoScoreStyle = css`
  opacity: 0.3;
`;

export {QualityScoreBar};
