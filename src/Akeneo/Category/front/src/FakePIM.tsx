import React, {ReactNode, useEffect} from 'react';
import styled from 'styled-components';
import {AkeneoIcon, CardIcon, CommonStyle, getColor, MainNavigationItem, SettingsIcon} from 'akeneo-design-system';
import {DependenciesContextProps, useDependenciesContext} from '@akeneo-pim-community/shared';
import {Configuration, useConfiguration} from './configuration';

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

const patchDeps = (deps: DependenciesContextProps, configuration: Configuration) => {
  deps.router = {
    ...deps.router,

    redirect: fragment => {
      window.location.hash = fragment.indexOf('#') === 0 ? fragment : '#' + fragment; // fires hashchange event which HashRouter listens to
    },
  } as typeof deps.router;

  deps.security = {
    isGranted: (acl: string) => configuration.acls[acl] === true,
  } as typeof deps.security;

  deps.featureFlags = {
    isEnabled: (feature: string) => {
      return configuration.features[feature] ?? false;
    },
  } as typeof deps.featureFlags;
};

const FakePIM = ({children}: Props) => {
  const deps = useDependenciesContext();
  const {configuration} = useConfiguration();

  // deps must be patched before CategoryApp is rendered !
  // can't wait for effect to be applied
  patchDeps(deps, configuration);

  useEffect(() => {
    patchDeps(deps, configuration);
  }, [deps, configuration]);

  return (
    <Container>
      <Menu>
        <LogoContainer>
          <AkeneoIcon size={36} />
        </LogoContainer>
        <MainNavigationItem href="#/" icon={<CardIcon />}>
          App
        </MainNavigationItem>
        <MainNavigationItem href="#/configuration" icon={<SettingsIcon />}>
          Configuration
        </MainNavigationItem>
      </Menu>
      <Page>{children}</Page>
    </Container>
  );
};

export {FakePIM};
