import React, {useEffect} from 'react';
import styled from 'styled-components';
import {AkeneoIcon, CardIcon, CommonStyle, getColor, MainNavigationItem, SettingsIcon} from 'akeneo-design-system';
import {useDependenciesContext} from '@akeneo-pim-community/shared';

const Container = styled.div`
  display: flex;
  width: 100vw;
  height: 100vh;

  ${CommonStyle}
`;

const Menu = styled.div`
  display: flex;
  flex-direction: column;
  justify-content: start;
  width: 80px;
  height: 100vh;
  border-right: 1px solid ${getColor('grey', 60)};
  color: ${getColor('brand', 100)};
`;

const Page = styled.div`
  flex: 1;
  padding: 40px;
`;


const LogoContainer = styled.div`
  display: flex;
  justify-content: center;
  height: 80px;
  align-items: center;
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
        <LogoContainer>
          <AkeneoIcon size={36}/>
        </LogoContainer>
        <MainNavigationItem
          href="#/"
          icon={<CardIcon/>}
        >
          App
        </MainNavigationItem>
        <MainNavigationItem
          href="#/configuration"
          icon={<SettingsIcon/>}
        >
          Configuration
        </MainNavigationItem>
      </Menu>
      <Page>{children}</Page>
    </Container>
  );
};

export {FakePIM};
