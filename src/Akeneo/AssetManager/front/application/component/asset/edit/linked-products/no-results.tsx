import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

const Container = styled.div`
  align-items: center;
  display: flex;
  flex-direction: column;
  margin: 20px 0 0;
  overflow: hidden;
`;

const Image = styled.div`
  background-image: url('/bundles/pimui/images/illustrations/Product.svg');
  background-position: center center;
  background-repeat: no-repeat;
  background-size: 100% auto;
  height: 128px;
  width: 128px;
`;

const Message = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.big};
  line-height: 24px;
  margin-bottom: 20px;
  max-width: 600px;
  text-align: center;
`;

export const NoResults = React.memo(({message}: {message: string}) => {
  return (
    <Container>
      <Image />
      <Message>{message}</Message>
    </Container>
  );
});
