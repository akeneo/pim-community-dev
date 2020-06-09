import ReactDOM from 'react-dom';
import React, {FC} from 'react';
import {act} from '@testing-library/react-hooks';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Card} from '@akeneo-pim-community/communication-channel/src/models/card';

const DefaultProviders: FC = ({children}) => {
  return (
    <DependenciesProvider>
      <AkeneoThemeProvider>
        {children}
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
};

export const createWithProviders = (nextElement: React.ReactElement) => <DefaultProviders>{nextElement}</DefaultProviders>;

export const renderWithProviders = (ui: React.ReactElement, container: HTMLElement) => ReactDOM.render(createWithProviders(ui), container);

export const getExpectedCards = () => {
  return [
    {
      title: 'Title card',
      description: 'Description card',
      img: '/path/img/card.png',
      altImg: 'alt card img',
      link: 'http://external.com#link-card',
      tags: ['new', 'updates'],
      startDate: '20-04-2020',
      notificationDuration: 7
    },
    {
      title: 'Title card 2',
      description: 'Description card 2',
      link: 'http://external.com#link-card-2',
      tags: ['tag'],
      startDate: '20-04-2020',
      notificationDuration: 14
    }
  ];
}

export const getExpectedCampaign = () => {
  return 'Serenity';
}

export const getMockDataProvider = () => {
  return {
    cardFetcher:  {
      fetchAll: (): Promise<Card[]> => {
        return new Promise(resolve => {
          act(() => {
            setTimeout(() => resolve(getExpectedCards()), 100);
          });
        })
      }
    },
    campaignFetcher: {
      fetch: (): Promise<string> => {
        return new Promise(resolve => {
          act(() => {
            setTimeout(() => resolve(getExpectedCampaign()), 100);
          });
        });
      } 
    }
  };
}
