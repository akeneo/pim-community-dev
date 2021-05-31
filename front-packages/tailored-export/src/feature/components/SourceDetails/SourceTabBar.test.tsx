import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders, Channel} from '@akeneo-pim-community/shared';
import {SourceTabBar} from './SourceTabBar';
import {Source} from '../../models';
import {fireEvent} from '@testing-library/dom';
import {FetcherContext, Attribute} from '../../contexts';
import {act} from "react-dom/test-utils";

global.beforeEach(() => {
  const intersectionObserverMock = () => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
  });

  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});

const attributes: Attribute[] = [
  {
    code: 'name',
    labels: {fr_FR: 'French name', en_US: 'English name'},
    scopable: false,
    localizable: false,
  },
  {
    code: 'description',
    labels: {fr_FR: 'French description', en_US: 'English description'},
    scopable: false,
    localizable: false,
  },
];

const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute>(attributes)},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve([])},
};

test('it renders the source tab bar', async () => {
  const handleTabChange = jest.fn();
  const sources: Source[] = [
    {
      uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
      code: 'description',
      type: 'attribute',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
      },
    },
    {
      uuid: 'cffd540e-1e40-4c55-a415-89c7958b270d',
      code: 'name',
      type: 'attribute',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
      },
    },
    {
      uuid: 'cffd540e-1e40-4c55-a415-89c7958b280d',
      code: 'category',
      type: 'property',
      locale: null,
      channel: null,
      operations: [],
      selection: {
        type: 'code',
      },
    },
  ];

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <SourceTabBar sources={sources} currentTab="cffd560e-1e40-4c55-a415-89c7958b270d" onTabChange={handleTabChange} />
      </FetcherContext.Provider>
    )
  });

  expect(screen.getByText(/English description/i)).toBeInTheDocument();
  expect(screen.getByText(/English name/i)).toBeInTheDocument();
  expect(screen.getByText(/pim_common.category/i)).toBeInTheDocument();

  fireEvent.click(screen.getByText(/Name/i));
  expect(handleTabChange).toHaveBeenCalledWith('cffd540e-1e40-4c55-a415-89c7958b270d');
});
