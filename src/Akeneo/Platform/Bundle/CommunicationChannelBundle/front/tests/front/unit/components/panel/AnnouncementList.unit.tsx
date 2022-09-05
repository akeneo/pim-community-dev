import React from 'react';
import {act, getByText, getAllByText} from '@testing-library/react';
import {AnnouncementList} from '@akeneo-pim-community/communication-channel/src/components/panel/AnnouncementList';
import {formatCampaign} from '@akeneo-pim-community/communication-channel/src/tools/formatCampaign';
import {renderDOMWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {getExpectedAnnouncements, getExpectedPimAnalyticsData} from '../../__mocks__/dataProvider';
import {useHasNewAnnouncements} from '@akeneo-pim-community/communication-channel/src/hooks/useHasNewAnnouncements';
import {useInfiniteScroll} from '@akeneo-pim-community/communication-channel/src/hooks/useInfiniteScroll';
import {useAddViewedAnnouncements} from '@akeneo-pim-community/communication-channel/src/hooks/useAddViewedAnnouncements';

jest.mock('@akeneo-pim-community/communication-channel/src/hooks/useHasNewAnnouncements');
jest.mock('@akeneo-pim-community/communication-channel/src/hooks/useInfiniteScroll');
jest.mock('@akeneo-pim-community/communication-channel/src/hooks/useAddViewedAnnouncements');

const expectedAnnouncements = getExpectedAnnouncements();
const expectedPimAnalyticsData = getExpectedPimAnalyticsData();

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);

  const handleHasNewAnnouncements = jest.fn();
  useHasNewAnnouncements.mockReturnValue(handleHasNewAnnouncements);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('it shows the announcements when we open the panel', async () => {
  const campaign = formatCampaign(expectedPimAnalyticsData.pim_edition, expectedPimAnalyticsData.pim_version);
  const handleFetchingResults = jest.fn();
  useInfiniteScroll.mockReturnValue([
    {
      items: expectedAnnouncements,
      isFetching: false,
      hasError: false,
    },
    handleFetchingResults,
  ]);

  await act(async () =>
    renderDOMWithProviders(<AnnouncementList campaign={campaign} panelIsClosed={false} />, container as HTMLElement)
  );

  expect(container.querySelectorAll('ul li').length).toEqual(2);
});

test('it shows an empty list when there are no announcements', async () => {
  const campaign = formatCampaign(expectedPimAnalyticsData.pim_edition, expectedPimAnalyticsData.pim_version);
  const handleFetchingResults = jest.fn();
  useInfiniteScroll.mockReturnValue([
    {
      items: [],
      isFetching: false,
      hasError: false,
    },
    handleFetchingResults,
  ]);

  await act(async () =>
    renderDOMWithProviders(<AnnouncementList campaign={campaign} panelIsClosed={false} />, container as HTMLElement)
  );

  expect(container.querySelectorAll('ul li').length).toEqual(0);
  expect(getByText(container, 'akeneo_communication_channel.panel.list.empty')).toBeInTheDocument();
});

test('it can show for each announcement the information from the json', async () => {
  const campaign = formatCampaign(expectedPimAnalyticsData.pim_edition, expectedPimAnalyticsData.pim_version);
  useInfiniteScroll.mockReturnValue([
    {
      items: expectedAnnouncements,
      isFetching: false,
      hasError: false,
    },
    jest.fn(),
  ]);

  await act(async () =>
    renderDOMWithProviders(<AnnouncementList campaign={campaign} panelIsClosed={false} />, container as HTMLElement)
  );

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
  useInfiniteScroll.mockReturnValue([
    {
      items: expectedAnnouncements,
      isFetching: false,
      hasError: false,
    },
    jest.fn(),
  ]);

  await act(async () =>
    renderDOMWithProviders(<AnnouncementList campaign={campaign} panelIsClosed={false} />, container as HTMLElement)
  );

  expect((container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`) as HTMLLinkElement).href).toEqual(
    `http://external.com/?utm_source=akeneo-app&utm_medium=communication-panel&utm_content=${expectedAnnouncements[0].id}&utm_campaign=${campaign}`
  );
  expect((container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`) as HTMLLinkElement).target).toEqual(
    '_blank'
  );
});

test('it does not generate Read more button when there is no link.', async () => {
  const campaign = formatCampaign(expectedPimAnalyticsData.pim_edition, expectedPimAnalyticsData.pim_version);
  useInfiniteScroll.mockReturnValue([
    {
      items: expectedAnnouncements,
      isFetching: false,
      hasError: false,
    },
    jest.fn(),
  ]);

  await act(async () =>
    renderDOMWithProviders(<AnnouncementList campaign={campaign} panelIsClosed={false} />, container as HTMLElement)
  );

  expect(container.querySelectorAll('ul li a').length).toEqual(1);
});

test('it can display a message when it has an error during the fetch', async () => {
  const campaign = formatCampaign(expectedPimAnalyticsData.pim_edition, expectedPimAnalyticsData.pim_version);
  useInfiniteScroll.mockReturnValue([
    {
      items: [],
      isFetching: false,
      hasError: true,
    },
    jest.fn(),
  ]);

  await act(async () =>
    renderDOMWithProviders(<AnnouncementList campaign={campaign} panelIsClosed={false} />, container as HTMLElement)
  );

  expect(container.querySelectorAll('ul li').length).toEqual(0);
  expect(getByText(container, 'akeneo_communication_channel.panel.list.error')).toBeInTheDocument();
});

test('it updates the new announcements when closing the panel', async () => {
  const campaign = formatCampaign(expectedPimAnalyticsData.pim_edition, expectedPimAnalyticsData.pim_version);
  const handleHasNewAnnouncements = jest.fn();
  useHasNewAnnouncements.mockReturnValue(handleHasNewAnnouncements);
  useInfiniteScroll.mockReturnValue([
    {
      items: expectedAnnouncements,
      isFetching: false,
      hasError: false,
    },
    jest.fn(),
  ]);
  const handleAddViewedAnnouncements = jest.fn();
  useAddViewedAnnouncements.mockReturnValue(handleAddViewedAnnouncements);

  await act(async () =>
    renderDOMWithProviders(<AnnouncementList campaign={campaign} panelIsClosed={true} />, container as HTMLElement)
  );

  expect(handleAddViewedAnnouncements).toBeCalledWith([expectedAnnouncements[0]]);
  expect(handleHasNewAnnouncements).toBeCalledTimes(1);
});
