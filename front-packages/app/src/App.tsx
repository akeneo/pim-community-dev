import React from 'react';
import styled from 'styled-components';
import {
  AkeneoIcon,
  CardIcon,
  CommonStyle,
  DownloadIcon,
  getColor,
  MainNavigationItem,
  ProductIcon,
  SystemIcon,
  UploadIcon,
} from 'akeneo-design-system';
import {MeasurementApp} from '@akeneo-pim-community/measurement';
import {HashRouter as Router, Route, Switch} from 'react-router-dom';
import {Legacy} from './feature/Legacy';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {ProcessTrackerApp} from '@akeneo-pim-community/process-tracker';

const Container = styled.div`
  display: flex;
  width: 100vw;
  height: 100vh;

  ${CommonStyle}
`;

const Logo = styled(AkeneoIcon)`
  margin: 15px 0;
`;

const Menu = styled.div`
  display: flex;
  justify-content: center;
  flex-direction: column;
  justify-content: start;
  align-items: center;
  width: 80px;
  height: 100vh;
  border-right: 1px solid ${getColor('grey', 60)};
  color: ${getColor('brand', 100)};
`;

const Page = styled.div`
  flex: 1;
`;

const App = () => {
  const translate = useTranslate();
  const router = useRouter();
  return (
    <Container>
      <Menu>
        <Logo size={36} />
        <MainNavigationItem href={`#${router.generate('pim_dashboard_index')}`} icon={<CardIcon />}>
          {translate('pim_menu.tab.activity')}
        </MainNavigationItem>
        <MainNavigationItem href={`#${router.generate('pim_enrich_product_index')}`} icon={<ProductIcon />}>
          {translate('pim_enrich.entity.product.plural_label')}
        </MainNavigationItem>
        <MainNavigationItem
          href={`#${router.generate('pim_importexport_import_profile_index')}`}
          icon={<DownloadIcon />}
        >
          {translate('pim_menu.tab.imports')}
        </MainNavigationItem>
        <MainNavigationItem href={`#${router.generate('pim_importexport_export_profile_index')}`} icon={<UploadIcon />}>
          {translate('pim_menu.tab.exports')}
        </MainNavigationItem>
        <MainNavigationItem href={`#${router.generate('pim_settings_index')}`} icon={<UploadIcon />}>
          {translate('pim_menu.tab.settings')}
        </MainNavigationItem>
        <MainNavigationItem href={`#${router.generate('pim_system_index')}`} icon={<SystemIcon />}>
          {translate('pim_menu.tab.system')}
        </MainNavigationItem>
      </Menu>
      <Page>
        <Router>
          <Switch>
            <Route path="/configuration/measurement">
              <MeasurementApp />
            </Route>
            <Route path="/job">
              <ProcessTrackerApp />
            </Route>
            <Route path="*">
              <Legacy />
            </Route>
          </Switch>
        </Router>
      </Page>
    </Container>
  );
};

export {App};
