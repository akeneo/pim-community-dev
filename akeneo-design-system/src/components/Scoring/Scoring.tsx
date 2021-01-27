import React, {Ref} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColorForScoring, getFontSize, Score} from '../../theme';

const ScoringContainer = styled.div<ScoringProps & AkeneoThemedProps>`
  display: flex;
  justify-content: center;
  align-items: center;
`;

const ScoreWrapper = styled.div<{score: Score} & ScoringProps & AkeneoThemedProps>`
  text-transform: uppercase;
  width: 20px;
  transform: ${({activeScore, score}) => (activeScore === score ? 'scale(1.25)' : 'scale(1)')};
  z-index: ${({activeScore, score}) => (activeScore === score ? '1' : '0')};
  background: ${({score}) => getColorForScoring(score, score === 'a' || score === 'c' || score === 'd' ? 20 : 60)};
  border-radius: ${({activeScore, score}) => (activeScore === score ? '4px' : '0')};
  border-top-left-radius: ${({activeScore, score}) => (score === 'a' || activeScore === score ? '4px' : '0')};
  border-bottom-left-radius: ${({activeScore, score}) => (score === 'a' || activeScore === score ? '4px' : '0')};
  border-top-right-radius: ${({activeScore, score}) => (score === 'e' || activeScore === score ? '4px' : '0')};
  border-bottom-right-radius: ${({activeScore, score}) => (score === 'e' || activeScore === score ? '4px' : '0')};
  display: flex;
  justify-content: center;
  align-items: center;

  p {
    font-size: ${({activeScore, score}) => (activeScore === score ? getFontSize('big') : getFontSize('default'))};
    color: ${({score}) =>
      getColorForScoring(score, score === 'a' || score === 'c' ? 120 : score === 'b' || score === 'e' ? 140 : 100)};
    margin: 0;
  }
`;

type ScoringProps = {
  /**
   * Active the Score defining its size.
   */
  activeScore?: Score;
};

/**
 * Scoring are introduced as new components inside Akeneo to highlight to the users the level of quality of their
 * product data. This components is specifically link to this usage. A stands for the best score and E for the worst
 * score. This components is based on the US notation system. The colors are used to support the understanding of the
 * score you have.
 */
const Scoring = React.forwardRef<HTMLDivElement, ScoringProps>(
  ({activeScore, ...rest}: ScoringProps, forwardedRef: Ref<HTMLDivElement>) => {
    const scores: Score[] = ['a', 'b', 'c', 'd', 'e'];
    return (
      <ScoringContainer ref={forwardedRef} {...rest}>
        {scores.map(score => (
          <ScoreWrapper key={score} score={score} activeScore={activeScore}>
            <p>{score}</p>
          </ScoreWrapper>
        ))}
      </ScoringContainer>
    );
  }
);

export {Scoring};
