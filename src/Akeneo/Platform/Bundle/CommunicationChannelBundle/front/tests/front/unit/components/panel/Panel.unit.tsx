import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act, getByText, getAllByText, getByTitle} from '@testing-library/react';
import {Panel} from '@akeneo-pim-community/communication-channel/src/components/panel';
import {formatCampaign} from '@akeneo-pim-community/communication-channel/src/tools/formatCampaign';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {renderWithProviders, fetchMockResponseOnce} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {getExpectedAnnouncements, getExpectedPimAnalyticsData} from '../../__mocks__/dataProvider';
import {getByTestId} from '@testing-library/dom';

const expectedAnnouncements = getExpectedAnnouncements();
const expectedPimAnalyticsData = getExpectedPimAnalyticsData();

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('it shows the panel with the first announcements', async () => {
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));
  fetchMockResponseOnce('/rest/announcements?limit=10', JSON.stringify({items: expectedAnnouncements}));

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  expect(getByText(container, 'akeneo_communication_channel.panel.title')).toBeInTheDocument();
  expect(container.querySelectorAll('ul li').length).toEqual(2);
});

test('it shows the panel with the next announcements', async () => {
  const nextItems = [
    {
      id: 'test-id-3',
      title: 'Title announcement 3',
      description: 'Description announcement 3',
      img: '/path/img/announcement.png',
      altImg: 'alt announcement img',
      link: 'http://external-3.com',
      tags: ['new', 'updates'],
      startDate: '20-04-2020',
    },
    {
      id: 'test-id-4',
      title: 'Title announcement 4',
      description: 'Description announcement 4',
      link: 'http://external-4.com',
      img: null,
      altImg: null,
      tags: ['tag'],
      startDate: '20-04-2020',
    },
  ];

  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));
  fetchMockResponseOnce('/rest/announcements?limit=10', JSON.stringify({items: expectedAnnouncements}));
  fetchMockResponseOnce('/rest/announcements?limit=10&search_after=test-id-2', JSON.stringify({items: nextItems}));

  await act(async () =>
    renderWithProviders(
      <div data-testid="wrapper">
        <Panel />
      </div>,
      container as HTMLElement
    )
  );

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'wrapper'), {target: {scrollY: 100}});
  });

  expect(container.querySelectorAll('ul li').length).toEqual(4);
});

test('it can show for each announcement the information from the json', async () => {
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));
  fetchMockResponseOnce('/rest/announcements?limit=10', JSON.stringify({items: expectedAnnouncements}));

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  expect(getByText(container, expectedAnnouncements[0].title)).toBeInTheDocument();
  expect(getByText(container, expectedAnnouncements[0].description)).toBeInTheDocument();
  expectedAnnouncements[0].tags.map(tag => {
    expect(getByText(container, tag)).toBeInTheDocument();
  });
  expect(getAllByText(container, expectedAnnouncements[0].startDate).length).toEqual(2);
  expect(container.querySelector(`img[alt="${expectedAnnouncements[0].altImg}"]`)).toBeInTheDocument();
  expect(container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`)).toBeInTheDocument();
});

test('it can open the read more link in a new tab', async () => {
  const campaign = formatCampaign(expectedPimAnalyticsData.pim_edition, expectedPimAnalyticsData.pim_version);
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));
  fetchMockResponseOnce('/rest/announcements?limit=10', JSON.stringify({items: expectedAnnouncements}));

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  expect((container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`) as HTMLLinkElement).href).toEqual(
    `http://external.com/?utm_source=akeneo-app&utm_medium=communication-panel&utm_content=${expectedAnnouncements[0].id}&utm_campaign=${campaign}`
  );
  expect((container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`) as HTMLLinkElement).target).toEqual(
    '_blank'
  );
});

test('it can display a message when it has an error during the fetch', async () => {
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));
  fetchMockResponseOnce('/rest/announcements?limit=10', JSON.stringify({items: 'error'}));

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  expect(container.querySelectorAll('ul li').length).toEqual(0);
  expect(getByText(container, 'akeneo_communication_channel.panel.list.error')).toBeInTheDocument();
});

test('it can display an empty panel when it is not a serenity version', async () => {
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify({pim_edition: 'CE', pim_version: '4.0'}));
  fetchMockResponseOnce('/rest/announcements?limit=10', JSON.stringify({items: expectedAnnouncements}));

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  expect(container.querySelectorAll('ul li').length).toEqual(0);
  expect(getByText(container, 'akeneo_communication_channel.panel.list.empty')).toBeInTheDocument();
});

test('it can close the panel', async () => {
  fetchMockResponseOnce('pim_analytics_data_collect', JSON.stringify(expectedPimAnalyticsData));
  fetchMockResponseOnce('/rest/announcements', JSON.stringify({items: []}));

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  fireEvent.click(getByTitle(container, 'pim_common.close'));

  expect(dependencies.mediator.trigger).toHaveBeenCalledWith('communication-channel:panel:close');
});
