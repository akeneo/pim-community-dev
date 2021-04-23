import React, {FC} from 'react';
import styled from 'styled-components';

const Container = styled.div`
  position: relative;
  width: 142px;
  height: 142px;
  border: 1px solid ${({theme}) => theme.color.grey80};
  margin-right: 20px;
  border-radius: 4px;
  display: flex;
  overflow: hidden;
  flex-basis: 142px;
  flex-shrink: 0;

  img {
    max-height: 140px;
    max-width: 140px;
    width: auto;
  }
`;

type IllustrationProps = {
  src: string;
  title?: string;
};

const Illustration: FC<IllustrationProps> = ({src, title = ''}) => {
  return (
    <Container>
      <img src={src} alt={title} />
    </Container>
  );
};

export {Illustration};
export type {IllustrationProps};
