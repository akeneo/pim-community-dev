import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act, getByText, getAllByText, getByTitle, waitForDomChange} from '@testing-library/react';
import {Panel} from '@akeneo-pim-community/communication-channel/src/components/panel';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {renderWithProviders, getMockDataProvider} from '../../../../test-utils';

const mockDataProvider = getMockDataProvider();
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

  expect(getByText(container, 'Title card')).toBeInTheDocument();
  expect(getByText(container, 'Description card')).toBeInTheDocument();
  expect(getByText(container, 'new')).toBeInTheDocument();
  expect(getAllByText(container, '20-04-2020').length).toEqual(2);
  expect(container.querySelector('img[alt="alt card img"]')).toBeInTheDocument();
  expect(container.querySelector('a[title="link-card"]')).toBeInTheDocument();
});

test('it can open the read more link in a new tab', async () => {
  await act(async () => renderWithProviders(
    <Panel dataProvider={mockDataProvider} />,
    container as HTMLElement
  ));

  await waitForDomChange({container});

  expect(container.querySelector('a[title="link-card"]').href).toEqual('http://external.com/?utm_source=akeneo-app&utm_medium=communication-panel&utm_campaign=Serenity#link-card');
  expect(container.querySelector('a[title="link-card"]').target).toEqual('_blank');
});

test('it can close the panel', async () => {
  await act(async () => renderWithProviders(
    <Panel dataProvider={mockDataProvider} />,
    container as HTMLElement
  ));

  fireEvent.click(getByTitle(container, 'pim_common.close'));

  expect(dependencies.mediator.trigger).toHaveBeenCalledWith('communication-channel:panel:close');
});
