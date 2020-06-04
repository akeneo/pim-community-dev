import React from 'react';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {Panel} from 'akeneocommunicationchannel/components/panel';
import {CardFetcherImplementation} from 'akeneocommunicationchannel/fetcher/card';
import {CampaignFetcherImplementation} from 'akeneocommunicationchannel/fetcher/campaign';
import {PanelDataProvider} from 'akeneocommunicationchannel/components/panel';

const dataProvider: PanelDataProvider = {
  cardFetcher: CardFetcherImplementation,
  campaignFetcher: CampaignFetcherImplementation
};

const Index = () => (
  <AkeneoThemeProvider>
    <Panel dataProvider={dataProvider} />
  </AkeneoThemeProvider>
);

export {Index};
