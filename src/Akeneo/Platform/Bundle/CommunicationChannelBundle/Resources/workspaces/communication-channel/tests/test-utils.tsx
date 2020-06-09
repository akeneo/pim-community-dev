import ReactDOM from 'react-dom';
import React, {FC} from 'react';
import {act} from '@testing-library/react-hooks';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Announcement} from '@akeneo-pim-community/communication-channel/src/models/announcement';

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

export const getExpectedAnnouncements = () => {
  return [
    {
      title: 'Title announcement',
      description: 'Description announcement',
      img: '/path/img/announcement.png',
      altImg: 'alt announcement img',
      link: 'http://external.com',
      tags: ['new', 'updates'],
      startDate: '20-04-2020',
      notificationDuration: 7
    },
    {
      title: 'Title announcement 2',
      description: 'Description announcement 2',
      link: 'http://external-2.com',
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
    announcementFetcher:  {
      fetchAll: (): Promise<Announcement[]> => {
        return new Promise(resolve => {
          act(() => {
            setTimeout(() => resolve(getExpectedAnnouncements()), 100);
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
