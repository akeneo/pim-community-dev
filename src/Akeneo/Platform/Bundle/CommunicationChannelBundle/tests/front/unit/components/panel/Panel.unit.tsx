import React from 'react';
import ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act, getByText, getAllByText, getByAltText, getByTitle} from '@testing-library/react';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {Panel} from 'akeneocommunicationchannel/components/panel/Panel';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const mediator = require('oro/mediator');

const cards = [
  {
    title: 'Title card',
    description: 'Description card',
    img: '/path/img/card.png',
    link: 'http://external.com#link-card',
    tags: ['new', 'updates'],
    date: '20-04-2020'
  },
  {
    title: 'Title card 2',
    description: 'Description card 2',
    img: '/path/img/card-2.png',
    link: 'http://external.com#link-card-2',
    tags: [],
    date: '20-04-2020'
  }
];
const campaign = ['Serenity'];
const dataProvider = {
  cardFetcher: {
    fetchAll: jest.fn(() => cards)
  },
  campaignFetcher: {
    fetch: jest.fn(() => campaign)
  }
};

let container;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('it shows the panel with the cards', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <Panel dataProvider={dataProvider} />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(getByText(container, 'akeneo_communication_channel.panel.title')).toBeInTheDocument();
  expect(container.querySelectorAll('ul li').length).toEqual(2);
});

test('it can show for each card the information from the json', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <Panel dataProvider={dataProvider} />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(getByText(container, 'Title card')).toBeInTheDocument();
  expect(getByText(container, 'Description card')).toBeInTheDocument();
  expect(getByText(container, 'new')).toBeInTheDocument();
  expect(getAllByText(container, '20-04-2020').length).toEqual(2);
  expect(container.querySelector('img[alt="card.png"]')).toBeInTheDocument();
  expect(container.querySelector('a[title="link-card"]')).toBeInTheDocument();
});

test('it can open the read more link in a new tab', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <Panel dataProvider={dataProvider} />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  expect(container.querySelector('a[title="link-card"]').href).toEqual('http://external.com/?utm_source=akeneo-app&utm_medium=communication-panel&utm_campaign=Serenity#link-card');
  expect(container.querySelector('a[title="link-card"]').target).toEqual('_blank');
});

test('it can close the panel', async () => {
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <Panel dataProvider={dataProvider} />
        </AkeneoThemeProvider>
      </DependenciesProvider>,
      container
    );
  });

  fireEvent.click(getByTitle(container, 'pim_common.close'));

  expect(mediator.trigger).toHaveBeenCalledWith('communication-channel:panel:close');
});
