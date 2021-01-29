import React, {Ref} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, Score} from '../../theme';
import {ScoreBar} from './Bar/ScoreBar';
import {ScoreCell} from './Cell/ScoreCell';

const ScoringContainer = styled.div<ScoringProps & AkeneoThemedProps>`
  display: flex;
  justify-content: center;
  align-items: center;
`;

type ScoringProps = {
  /**
   * Defines if Score should be highlighted.
   */
  activeScore?: Score;
  /**
   * Defines if Score should be a cell or a bar.
   */
  bar?: boolean;
};

/**
 * This component highlight to the users the level of quality of their product data.
 */
const Scoring = React.forwardRef<HTMLDivElement, ScoringProps>(
  ({activeScore, bar, ...rest}: ScoringProps, forwardedRef: Ref<HTMLDivElement>) => {
    console.log('bar: ', bar);
    return (
      <ScoringContainer ref={forwardedRef} {...rest}>
        {bar ? <ScoreBar activeScore={activeScore} /> : <ScoreCell score={activeScore} />}
      </ScoringContainer>
    );
  }
);

export {Scoring};
