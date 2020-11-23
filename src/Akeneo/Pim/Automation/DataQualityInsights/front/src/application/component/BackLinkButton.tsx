import React from 'react';
import styled from 'styled-components';

const Router = require('pim/router');

interface BackLinkButtonProps {
  label: string;
  route: string;
  routeParams?: [];
}
const Button = styled.div`
  top: -4px;
  margin-right: 10px;
`;

const BackLinkButton = ({label, route, routeParams}: BackLinkButtonProps) => {
  return (
    <Button className="AknButton AknButton--micro" onClick={() => Router.redirectToRoute(route, routeParams)}>
      {label}
    </Button>
  );
};

export {BackLinkButton};
