import React, {FC} from 'react';
import styled, {css} from 'styled-components';

type Props = {
  score: string | null;
  appearance: 'regular' | 'stacked';
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
        top: -2,
        left: 0,
      }
    : {top: 0, left: 0};

  return (
    <Wrapper isSelected={isSelected}>
      {appearance === 'stacked' && isSelected && (
        <>
          <Container
            isSelected
            className={className}
            style={{top: topStackStyle.top - 4, left: topStackStyle.left + 4}}
          />
          <Container
            isSelected
            className={className}
            style={{top: topStackStyle.top - 2, left: topStackStyle.left + 2}}
          />
        </>
      )}
      <Container isSelected={isSelected} score={score} className={className} style={topStackStyle}>
        {score}
      </Container>
    </Wrapper>
  );
};

const Wrapper = styled.div<{isSelected?: boolean}>`
  position: relative;
  ${props => (props.isSelected ? bigSize : standardSize)};
`;

const Container = styled.div<{score?: string; isSelected?: boolean}>`
  position: absolute;
  text-align: center;
  display: inline-block;
  text-transform: uppercase;
  font-weight: bold;
  border-radius: 4px;

  ${props => (props.isSelected ? bigSizeContainer : standardSizeContainer)}

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

const standardSizeContainer = css`
  ${standardSize}
  font-size: 13px;
`;

const bigSizeContainer = css`
  ${bigSize}
  font-size: 15px;
  lineHeight: 25px,
  border-radius: 4px !important;
  border: thin solid black;
`;

const AScore = css`
  background: ${({theme}) => theme.color.green20};
  color: ${({theme}) => theme.color.green120};
`;
const BScore = css`
  background: ${({theme}) => theme.color.green60};
  color: ${({theme}) => theme.color.green140};
`;
const CScore = css`
  background: ${({theme}) => theme.color.yellow20};
  color: ${({theme}) => theme.color.yellow120};
`;
const DScore = css`
  background: ${({theme}) => theme.color.red20};
  color: ${({theme}) => theme.color.red100};
`;
const EScore = css`
  background: ${({theme}) => theme.color.red60};
  color: ${({theme}) => theme.color.red140};
`;

export {QualityScore};
