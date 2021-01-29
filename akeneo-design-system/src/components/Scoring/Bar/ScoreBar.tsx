import React, {Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getFontSize, Score} from '../../../theme';
import {ScoreCell} from '../Cell/ScoreCell';

const ScoreBarContainer = styled.div<{score: Score} & ScoreBarProps & AkeneoThemedProps>`
  display: flex;
  position: relative;
  top: 1px;
  padding-right: 20px;
  margin-right: 20px;
  padding-top: 2px;
  height: 25px;

  ${score => score === null && NoScoreStyle}
`;

const NoScoreStyle = css`
  opacity: 0.3;
`;

const SelectedScore = styled(ScoreCell)`
  transform: scale(1.25);
  div > p {
    font-size: ${getFontSize('big')};
  }
`;

const UnselectedScore = styled(ScoreCell)<{score: Score}>`
  width: 20px;
  height: 20px;

  > :first-child {
    border-radius: ${({score}) => (score === 'a' ? '4px 0 0 4px' : '0')};
  }
  > :last-child {
    border-radius: 0 4px 4px 0;
    border-radius: ${({score}) => (score === 'e' ? '0 4px 4px 0' : '0')};
  }

  > :not(:first-child):not(:last-child) {
    border-radius: 0;
  }
`;

type ScoreBarProps = {
  /**
   * Defines if Score should be highlighted.
   */
  score?: Score;
};

/**
 * This component highlight to the users the level of quality of their product data.
 */
const ScoreBar = React.forwardRef<HTMLDivElement, ScoreBarProps>(
  ({score, ...rest}: ScoreBarProps, forwardedRef: Ref<HTMLDivElement>) => {
    const scores: Score[] = ['a', 'b', 'c', 'd', 'e'];
    return (
      <ScoreBarContainer ref={forwardedRef} {...rest} currentScore={score}>
        {scores.map((s: Score) => {
          return s === score ? (
            <SelectedScore key={`ranking-score-${s}`} score={score} />
          ) : (
            <UnselectedScore key={`ranking-score-${s}`} score={s} />
          );
        })}
      </ScoreBarContainer>
    );
  }
);

export {ScoreBar};
