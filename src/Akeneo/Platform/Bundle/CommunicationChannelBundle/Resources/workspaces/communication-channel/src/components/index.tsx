import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {Panel, PanelDataProvider} from './panel';
import {AnnouncementFetcher} from '../fetcher/announcement';

const dataProvider: PanelDataProvider = {
  announcementFetcher: AnnouncementFetcher
};

const Index = () => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <Panel dataProvider={dataProvider} />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

export {Index};
