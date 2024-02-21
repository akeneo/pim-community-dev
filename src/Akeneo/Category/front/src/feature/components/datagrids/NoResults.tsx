import React, {FC} from 'react';
import styled from 'styled-components';
import {NoResultsIllustration} from 'akeneo-design-system';

const Container = styled.div`
  margin-top: 120px;
  text-align: center;
`;

const Title = styled.div`
  font-size: ${({theme}) => theme.fontSize.title};
  color: ${({theme}) => theme.color.grey140};
  margin-top: 5px;
`;

const SubTitle = styled.div`
  font-size: ${({theme}) => theme.fontSize.bigger};
  color: ${({theme}) => theme.color.grey120};
  margin-top: 15px;
`;

type Props = {
  title: string;
  subtitle: string;
};

const NoResults: FC<Props> = ({title, subtitle}) => {
  return (
    <Container>
      <NoResultsIllustration size={256} />
      <Title>{title}</Title>
      <SubTitle>{subtitle}</SubTitle>
    </Container>
  );
};

export {NoResults};
