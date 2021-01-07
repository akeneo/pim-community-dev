import React, {FC, ReactElement} from 'react';
import styled from "styled-components";
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Props = {
  illustration: ReactElement;
  title: string;
  subtitle: string;
}

const EmptyDataPlaceholder: FC<Props> = ({illustration, title, subtitle}) => {
  const translate = useTranslate();

  return (
    <Container>
      {React.cloneElement(illustration)}
      <Title>{translate(title)}</Title>
      <Subtitle>{translate(subtitle)}</Subtitle>
    </Container>
  );
}

const Container = styled.div`
  text-align: center;
  font-size: ${({theme}) => theme.fontSize.big};
  margin: 10px 0 0 0;
  width: 100%;
`;

const Title = styled.div`
  font-size: ${({theme}) => theme.fontSize.title};
  color: ${({theme}) => theme.color.grey140};
  margin-bottom: 12px;
`;

const Subtitle = styled.div`
  font-size: ${({theme}) => theme.fontSize.bigger};
  color: ${({theme}) => theme.color.grey120};
`;

export {EmptyDataPlaceholder};
