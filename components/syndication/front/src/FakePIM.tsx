import React from 'react';
import styled from 'styled-components';
import {AkeneoIcon, CommonStyle, getColor} from 'akeneo-design-system';
import {Syndication} from './feature/configuration';
import {BrowserRouter as Router, Route, Switch} from 'react-router-dom';
import {Redirect, useRouteMatch} from 'react-router';

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

const Edit = () => {
  const match = useRouteMatch<{jobCode: string}>();

  return <Syndication jobCode={match.params.jobCode} />;
};

const FakePIM = () => {
  return (
    <Container>
      <Menu>
        <AkeneoIcon size={36} />
      </Menu>
      <Router>
        <Switch>
          <Route exact path="/">
            <Redirect to="/amazon_vendor_us" />
          </Route>
          <Route path="/:jobCode">
            <Edit />
          </Route>
        </Switch>
      </Router>
    </Container>
  );
};

export {FakePIM};
