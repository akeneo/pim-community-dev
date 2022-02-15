import React, {FC} from 'react';
import styled, {css} from 'styled-components';

type Props = {
  score: string | null;
  appearance?: 'regular' | 'stacked';
  className?: string;
  isSelected?: boolean;
};

// TODO remove className prop ? (seems unused)

const QualityScore: FC<Props> = ({score, className, isSelected, appearance = 'regular'}) => {
  if (score === 'N/A' || score === null) {
    return <>N/A</>;
  }

  const topStackStyle = isSelected
    ? {
        top: 0,
        left: -2,
      }
    : {top: 0, left: 0};

  return (
    <Wrapper isSelected={isSelected}>
      {appearance === 'stacked' && isSelected && (
        <>
          <Container
            isSelected
            className={className}
            score={score}
            displayBackground={false}
            style={{top: topStackStyle.top - 4, left: topStackStyle.left + 4}}
          />
          <Container
            isSelected
            className={className}
            score={score}
            displayBackground={false}
            style={{top: topStackStyle.top - 2, left: topStackStyle.left + 2}}
          />
        </>
      )}
      <Container
        isSelected={isSelected}
        score={score}
        className={className}
        style={topStackStyle}
        displayBackground={true}
      >
        {score}
      </Container>
    </Wrapper>
  );
};

const Wrapper = styled.div<{isSelected?: boolean}>`
  position: relative;
  ${props => (props.isSelected ? bigSize : standardSize)};
`;

const switchContainer = (score: any) => {
  switch (score) {
    case 'A': {
      return ABorderScore;
    }
    case 'B': {
      return BBorderScore;
    }
    case 'C': {
      return CBorderScore;
    }
    case 'D': {
      return DBorderScore;
    }
    case 'E': {
      return EBorderScore;
    }
    default:
      return 'black';
  }
};

const Container = styled.div<{score?: string; isSelected?: boolean; displayBackground?: boolean}>`
  position: absolute;
  text-align: center;
  display: inline-block;
  text-transform: uppercase;
  font-weight: bold;

  ${({isSelected, score}) =>
    isSelected
      ? css`
          width: 25px;
          height: 25px;
          font-size: 15px;
          line-height: 25px;
          border-radius: 4px !important;
          border: 1px solid ${switchContainer(score)};
        `
      : css`
          width: 20px;
          height: 20px;
          font-size: 12px;
          z-index: -1;
        `}

  ${props => props.score === 'A' && AScore}
  ${props => props.score === 'B' && BScore}
  ${props => props.score === 'C' && CScore}
  ${props => props.score === 'D' && DScore}
  ${props => props.score === 'E' && EScore}
`;

const standardSize = css`
  width: 20px;
  height: 20px;
`;

const bigSize = css`
  width: 25px;
  height: 25px;
`;

const AScore = css<{displayBackground: boolean}>`
  background: ${({theme, displayBackground}) => (displayBackground ? theme.color.green20 : theme.color.white)};
  color: ${({theme}) => theme.color.green120};
  border-radius: 4px 0 0 4px;
`;
const BScore = css<{displayBackground: boolean}>`
  background: ${({theme, displayBackground}) => (displayBackground ? theme.color.green60 : theme.color.white)};
  color: ${({theme}) => theme.color.green140};
`;
const CScore = css<{displayBackground: boolean}>`
  background: ${({theme, displayBackground}) => (displayBackground ? theme.color.yellow20 : theme.color.white)};
  color: ${({theme}) => theme.color.yellow120};
`;
const DScore = css<{displayBackground: boolean}>`
  background: ${({theme, displayBackground}) => (displayBackground ? theme.color.red20 : theme.color.white)};
  color: ${({theme}) => theme.color.red100};
`;
const EScore = css<{displayBackground: boolean}>`
  background: ${({theme, displayBackground}) => (displayBackground ? theme.color.red60 : theme.color.white)};
  color: ${({theme}) => theme.color.red140};
  border-radius: 0 4px 4px 0;
`;

const ABorderScore = css`
  ${({theme}) => theme.color.green60};
`;
const BBorderScore = css`
  ${({theme}) => theme.color.green100};
`;
const CBorderScore = css`
  ${({theme}) => theme.color.yellow60};
`;
const DBorderScore = css`
  ${({theme}) => theme.color.red40};
`;
const EBorderScore = css`
  ${({theme}) => theme.color.red100};
`;

export {QualityScore};
