import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoIcon, CommonStyle, getColor} from 'akeneo-design-system';

type Props = {
  children?: ReactNode;
};

const Container = styled.div`
  display: flex;
  width: 100vw;
  height: 100vh;

  ${CommonStyle}
`;

const Menu = styled.div`
  display: flex;
  justify-content: center;
  padding: 15px;
  width: 80px;
  height: 100vh;
  border-right: 1px solid ${getColor('grey', 60)};
  color: ${getColor('brand', 100)};
`;

const Page = styled.div`
  flex: 1;
  padding: 40px;
`;

const FakePIM = ({children}: Props) => {
  return (
    <Container>
      <Menu>
        <AkeneoIcon size={36} />
      </Menu>
      <Page>{children}</Page>
    </Container>
  );
};

export {FakePIM};
