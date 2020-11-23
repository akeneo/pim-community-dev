import React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneo-design-system';

const Router = require('pim/router');

interface BackLinkButtonProps {
  label: string;
  route: string;
  routeParams?: [];
}

const Container = styled.div`
  margin-top: -4px;
  margin-right: 10px;
`;

const BackLinkButton = ({label, route, routeParams}: BackLinkButtonProps) => {
  return (
    <Container>
      <Button ghost size="small" level={'tertiary'} onClick={() => Router.redirectToRoute(route, routeParams)}>
        {label}
      </Button>
    </Container>
  );
};

export {BackLinkButton};
