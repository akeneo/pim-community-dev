import React, {FC} from 'react';
import {QualityScore} from './QualityScore';
import styled, {css} from 'styled-components';
import {DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB} from '../listener';

type Props = {
  currentScore: string | null;
};

const QualityScoreBar: FC<Props> = ({currentScore}) => {
  return (
    <Container
      currentScore={currentScore}
      onClick={() => window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_REDIRECT_TO_DQI_TAB))}
    >
      {['A', 'B', 'C', 'D', 'E'].map((score: string) => {
        return score === currentScore ? (
          <SelectedScore key={`ranking-score-${currentScore}`} score={currentScore} />
        ) : (
          <QualityScore key={`ranking-score-${score}`} score={score} />
        );
      })}
    </Container>
  );
};

const Container = styled.div<Props>`
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

const SelectedScore = styled(QualityScore)`
  width: 25px;
  height: 25px;
  border-radius: 4px !important;
  line-height: 25px;
  font-size: 15px;
  top: -2px;
  position: relative;
  margin: 0 -2px 0 -2px;
`;

export {QualityScoreBar};
