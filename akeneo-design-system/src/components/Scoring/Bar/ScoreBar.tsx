import React, {Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, Score} from '../../../theme';
import {ScoreCell} from '../Cell/ScoreCell';

const getPlacementForScore = (score: Score) => {
  if (score === 'a') {
    return 'left';
  }
  if (score === 'e') {
    return 'right';
  }
  return 'middle';
};

const ScoreBarContainer = styled.div<ScoreBarProps & AkeneoThemedProps>`
  display: flex;
  position: relative;
  top: 1px;
  padding-top: 2px;
  height: 25px;

  ${({score}) =>
    (score === null || score === 'n/a') &&
    css`
      opacity: 0.3;
    `}
`;

type ScoreBarProps = {
  /**
   * Defines if Score should be highlighted.
   */
  score?: Score | 'n/a' | null;
};

/**
 * This component highlight to the users the level of quality of their product data.
 */
const ScoreBar = React.forwardRef<HTMLDivElement, ScoreBarProps>(
  ({score, ...rest}: ScoreBarProps, forwardedRef: Ref<HTMLDivElement>) => {
    const scores: Score[] = ['a', 'b', 'c', 'd', 'e'];
    return (
      <ScoreBarContainer ref={forwardedRef} {...rest} score={score}>
        {scores.map(
          (s: Score) =>
            s && (
              <ScoreCell
                key={`ranking-score-${s}`}
                score={s}
                placement={s === score ? undefined : getPlacementForScore(s)}
                bigger={s === score}
              />
            )
        )}
      </ScoreBarContainer>
    );
  }
);

export {ScoreBar};
