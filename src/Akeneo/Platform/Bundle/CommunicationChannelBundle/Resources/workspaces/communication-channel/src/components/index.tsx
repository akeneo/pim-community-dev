import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {Panel, PanelDataProvider} from './panel';
import {AnnouncementFetcherImplementation} from '../fetcher/announcement';
import {CampaignFetcherImplementation} from './../fetcher/campaign';

const dataProvider: PanelDataProvider = {
  announcementFetcher: AnnouncementFetcherImplementation,
  campaignFetcher: CampaignFetcherImplementation
};

const Index = () => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <Panel dataProvider={dataProvider} />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

export {Index};
