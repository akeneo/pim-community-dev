import React, {FC} from 'react';
import styled, {css} from 'styled-components';

type Props = {
  score: string | null;
  className?: string;
};

const QualityScore: FC<Props> = ({score, className}) => {
  if (score === 'N/A' || score === null) {
    return <>N/A</>;
  }

  return (
    <Container score={score} className={className}>
      {score}
    </Container>
  );
};

const Container = styled.div<{score: string}>`
  width: 20px;
  height: 20px;
  font-size: 13px;
  text-align: center;
  display: inline-block;
  text-transform: uppercase;
  font-weight: bold;
  border-radius: 4px;

  ${props => props.score === 'A' && AScore}
  ${props => props.score === 'B' && BScore}
  ${props => props.score === 'C' && CScore}
  ${props => props.score === 'D' && DScore}
  ${props => props.score === 'E' && EScore}
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
