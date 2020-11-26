import React, {FC} from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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
  justify-content: space-between;
  align-items: center;
  z-index: 2;
  border-bottom: 1px solid ${({theme}) => theme.color.grey140};
  margin-top: 20px;
`;

const SectionTitle: FC<Props> = ({title}) => {
  const translate = useTranslate();

  return (
    <Container>
      <span>{translate(title)}</span>
    </Container>
  );
};

export {SectionTitle};
