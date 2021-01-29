import React, { Ref } from 'react';
import styled from 'styled-components';
import { AkeneoThemedProps, getColorForScoring, getFontSize, Score } from '../../../theme';

const ScoreCellContainer = styled.div<{ score: Score } & ScoreCellProps & AkeneoThemedProps>`
  display: flex;
  justify-content: center;
  align-items: center;
  background: ${({ score }) => getColorForScoring(score, score === 'a' || score === 'c' || score === 'd' ? 20 : 60)};
  border-radius: 4px;
  width: 20px;
  height: 20px;
`;

const ScoreCellWrapper = styled.p<{ score: Score } & ScoreCellProps & AkeneoThemedProps>`
  font-size: ${getFontSize('default')};
  color: ${({ score }) =>
    getColorForScoring(score, score === 'a' || score === 'c' ? 120 : score === 'b' || score === 'e' ? 140 : 100)};
  margin: 0;
  font-weight: 900;
  text-transform: uppercase;
`;

type ScoreCellProps = {
  /**
   * Defines if Score should be highlighted.
   */
  score?: Score;
};

/**
 * This component highlight to the users the level of quality of their product data.
 */
const ScoreCell = React.forwardRef<HTMLDivElement, ScoreCellProps>(
  ({ score, ...rest }: ScoreCellProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <div ref={forwardedRef} {...rest}>
        {score && (
          <ScoreCellContainer score={score}>
            <ScoreCellWrapper score={score}>{score}</ScoreCellWrapper>
          </ScoreCellContainer>
        )}
      </div>
    );
  }
);

export { ScoreCell };
