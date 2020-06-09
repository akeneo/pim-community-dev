import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act, getByText, getAllByText, getByTitle, waitForDomChange} from '@testing-library/react';
import {Panel} from '@akeneo-pim-community/communication-channel/src/components/panel';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {renderWithProviders, getMockDataProvider, getExpectedAnnouncements, getExpectedCampaign} from '../../../../test-utils';

const mockDataProvider = getMockDataProvider();
const expectedAnnouncements = getExpectedAnnouncements();
const expectedCampaign = getExpectedCampaign();

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('it shows the panel with the cards', async () => {
  await act(async () => renderWithProviders(
    <Panel dataProvider={mockDataProvider} />,
    container as HTMLElement
  ));

  // @TODO : It will have to be changed by the method "waitFor" when we will bump the @testing-library/react version
  // Replaced and deprecated in the latest versions (https://testing-library.com/docs/dom-testing-library/api-async#waitfordomchange-deprecated-use-waitfor-instead)
  await waitForDomChange({container});

  expect(getByText(container, 'akeneo_communication_channel.panel.title')).toBeInTheDocument();
  expect(container.querySelectorAll('ul li').length).toEqual(2);
});

test('it can show for each card the information from the json', async () => {
  await act(async () => renderWithProviders(
    <Panel dataProvider={mockDataProvider} />,
    container as HTMLElement
  ));

  await waitForDomChange({container});

  expect(getByText(container, expectedAnnouncements[0].title)).toBeInTheDocument();
  expect(getByText(container, expectedAnnouncements[0].description)).toBeInTheDocument();
  expectedAnnouncements[0].tags.map((tag) => {
    expect(getByText(container, tag)).toBeInTheDocument();
  });
  expect(getAllByText(container, expectedAnnouncements[0].startDate).length).toEqual(2);
  expect(container.querySelector(`img[alt="${expectedAnnouncements[0].altImg}"]`)).toBeInTheDocument();
  expect(container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`)).toBeInTheDocument();
});

test('it can open the read more link in a new tab', async () => {
  await act(async () => renderWithProviders(
    <Panel dataProvider={mockDataProvider} />,
    container as HTMLElement
  ));

  await waitForDomChange({container});

  expect((container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`) as HTMLLinkElement).href).toEqual(`http://external.com/?utm_source=akeneo-app&utm_medium=communication-panel&utm_campaign=${expectedCampaign}`);
  expect((container.querySelector(`a[title="${expectedAnnouncements[0].title}"]`) as HTMLLinkElement).target).toEqual('_blank');
});

test('it can close the panel', async () => {
  await act(async () => renderWithProviders(
    <Panel dataProvider={mockDataProvider} />,
    container as HTMLElement
  ));

  fireEvent.click(getByTitle(container, 'pim_common.close'));

  expect(dependencies.mediator.trigger).toHaveBeenCalledWith('communication-channel:panel:close');
});
