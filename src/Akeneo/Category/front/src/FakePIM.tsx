import React, {useEffect} from 'react';
import styled from 'styled-components';
import {AkeneoIcon, CommonStyle, getColor} from 'akeneo-design-system';
import {useDependenciesContext} from '@akeneo-pim-community/shared';

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

const FakePIM = ({children}) => {
  const deps = useDependenciesContext();

  useEffect(() => {
    deps.router = {
      ...deps.router,

      redirect: fragment => {
        const normalizedFragment = fragment.indexOf('#') === 0 ? fragment : '#' + fragment;
        window.location.hash = normalizedFragment; // fires hashchange event which HashRouter listens to
      },

      redirectToRoute: function (route, routeParams, options) {
        return deps.router.redirect(deps.router.generate(route, routeParams), options);
      },
    } as typeof deps.router;
  }, [deps.router]);

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
