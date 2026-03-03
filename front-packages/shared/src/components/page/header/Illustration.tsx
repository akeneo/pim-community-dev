import React, {FC} from 'react';
import styled from 'styled-components';
import {Image} from 'akeneo-design-system';

const Container = styled.div`
  margin-right: 20px;
`;

type IllustrationProps = {
  src?: string;
  title?: string;
};

const Illustration: FC<IllustrationProps> = ({src, title = '', children}) =>
  src ? (
    <Container>
      <Image fit="contain" width={142} height={142} src={src} alt={title} />
    </Container>
  ) : (
    <>{children}</>
  );

export {Illustration};
export type {IllustrationProps};
