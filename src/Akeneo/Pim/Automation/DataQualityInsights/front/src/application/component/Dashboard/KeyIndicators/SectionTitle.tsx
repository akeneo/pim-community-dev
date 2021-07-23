import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  title: string;
};

const Container = styled.div`
  display: flex;
  background: ${({theme}) => theme.color.white};
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.grey140};
  text-transform: uppercase;
  font-weight: normal;
  line-height: 44px;
  height: 44px;
  align-items: center;
  z-index: 2;
  border-bottom: 1px solid ${({theme}) => theme.color.grey140};
  margin-top: 20px;
`;

const SectionTitle: FC<Props> = ({title, children}) => {
  const translate = useTranslate();

  return (
    <Container>
      <span>{translate(title)}</span>
      {children}
    </Container>
  );
};

export {SectionTitle};
