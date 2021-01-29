import React, { Ref } from 'react';
import styled from 'styled-components';
import { AkeneoThemedProps, Score } from '../../theme';
import { ScoreBar } from './Bar/ScoreBar';
import { ScoreCell } from './Cell/ScoreCell';

const ScoringContainer = styled.div<ScoringProps & AkeneoThemedProps>`
  display: flex;
  justify-content: center;
  align-items: center;
`;

type ScoringProps = {
  /**
   * Defines if Score should be highlighted.
   */
  score?: Score;
  /**
   * Defines if Score should be a cell or a bar.
   */
  bar?: boolean;
};

/**
 * This component highlight to the users the level of quality of their product data.
 */
const Scoring = React.forwardRef<HTMLDivElement, ScoringProps>(
  ({ score, bar, ...rest }: ScoringProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <ScoringContainer ref={forwardedRef} {...rest}>
        {bar ? <ScoreBar score={score} /> : <ScoreCell score={score} />}
      </ScoringContainer>
    );
  }
);

export { Scoring };
