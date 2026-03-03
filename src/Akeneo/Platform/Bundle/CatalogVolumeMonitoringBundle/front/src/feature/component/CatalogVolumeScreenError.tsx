import styled from 'styled-components';
import React from 'react';
import {ClientErrorIllustration, getColor, getFontFamily, getFontSize} from 'akeneo-design-system';

type CatalogVolumeScreenErrorProps = {
  title: string;
  message: string;
};

const Container = styled.div`
  max-width: 940px;
  margin: 10px auto;
  height: 80vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
`;

const Title = styled.h1`
  width: auto;
  height: 34px;
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
  font-family: ${getFontFamily('default')};
  font-weight: normal;
`;

const Image = styled.div`
  width: auto;
`;

const Message = styled.div`
  width: auto;
  height: 21px;
  color: ${getColor('grey', 120)};
  font-size: ${getFontSize('bigger')};
  font-family: ${getFontFamily('default')};
  font-weight: normal;
`;

const CatalogVolumeScreenError = ({title, message}: CatalogVolumeScreenErrorProps) => {
  return (
    <Container>
      <Image>
        <ClientErrorIllustration width="525px" height="255px" />
      </Image>
      <Title>{title}</Title>
      <Message>{message}</Message>
    </Container>
  );
};

export {CatalogVolumeScreenError};
