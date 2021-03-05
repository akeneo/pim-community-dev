import React, {Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColorForScoring, getFontSize, Score} from '../../../theme';

const getBackgroundGradient = (score: Score) => (score === 'a' || score === 'c' || score === 'd' ? 20 : 60);
const getColorGradient = (score: Score) =>
  score === 'a' || score === 'c' ? 120 : score === 'b' || score === 'e' ? 140 : 100;
const getBorderRadiusForPlacement = (placement: 'left' | 'right' | 'middle' | undefined) => {
  if (placement === undefined) {
    return '4px';
  }
  if (placement === 'left') {
    return '4px 0 0 4px';
  }
  if (placement === 'right') {
    return '0 4px 4px 0';
  }
  return 0;
};

const ScoreCellContainer = styled.div<{score: Score} & ScoreCellProps & AkeneoThemedProps>`
  display: flex;
  justify-content: center;
  align-items: center;
  background: ${({score}) => getColorForScoring(score, getBackgroundGradient(score))};
  border-radius: ${({placement}) => getBorderRadiusForPlacement(placement)};
  width: 20px;
  height: 20px;

  ${({bigger}) =>
    bigger &&
    css`
      transform: scale(1.25);
    `}
`;

const ScoreCellWrapper = styled.p<{score: Score} & ScoreCellProps & AkeneoThemedProps>`
  font-size: ${getFontSize('default')};
  color: ${({score}) => getColorForScoring(score, getColorGradient(score))};
  margin: 0;
  font-weight: 900;
  text-transform: uppercase;

  ${({bigger}) =>
    bigger &&
    css`
      transform: scale(0.8); // Cancel the scaling transformation from the parent container
      font-size: ${getFontSize('big')};
    `}
`;

type ScoreCellProps = {
  /**
   * Defines if Score should be highlighted.
   */
  score?: Score | 'n/a' | null;
  /**
   * Defines the placement of the cell when it used in ScoreBar
   */
  placement?: 'left' | 'right' | 'middle';
  /**
   * Defines if the score should be displayed bigger
   */
  bigger?: boolean;
};

/**
 * This component highlight to the users the level of quality of their product data.
 */
const ScoreCell = React.forwardRef<HTMLDivElement, ScoreCellProps>(
  ({score = null, placement, bigger = false, ...rest}: ScoreCellProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <div ref={forwardedRef} {...rest}>
        {score === null || score === 'n/a' ? (
          <>N/A</>
        ) : (
          <ScoreCellContainer score={score} placement={placement} bigger={bigger}>
            <ScoreCellWrapper score={score} bigger={bigger}>
              {score}
            </ScoreCellWrapper>
          </ScoreCellContainer>
        )}
      </div>
    );
  }
);

export {ScoreCell};
