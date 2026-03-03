import React from 'react';
import {fireEvent, act, getByText, getByTitle} from '@testing-library/react';
import {Panel} from '@akeneo-pim-community/communication-channel/src/components/panel';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {renderDOMWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {usePimVersion} from '@akeneo-pim-community/communication-channel/src/hooks/usePimVersion';
import {useHasNewAnnouncements} from '@akeneo-pim-community/communication-channel/src/hooks/useHasNewAnnouncements';
import {useInfiniteScroll} from '@akeneo-pim-community/communication-channel/src/hooks/useInfiniteScroll';

jest.mock('@akeneo-pim-community/communication-channel/src/hooks/usePimVersion');
jest.mock('@akeneo-pim-community/communication-channel/src/hooks/useHasNewAnnouncements');
jest.mock('@akeneo-pim-community/communication-channel/src/hooks/useInfiniteScroll');

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
});

test('it check if it has new announcements when the component is mounted', async () => {
  usePimVersion.mockReturnValue({
    data: {edition: 'Serenity', version: '192939349'},
    hasError: false,
  });
  const handleHasNewAnnouncements = jest.fn();
  useHasNewAnnouncements.mockReturnValue(handleHasNewAnnouncements);

  await act(async () => renderDOMWithProviders(<Panel />, container));

  expect(handleHasNewAnnouncements).toBeCalledTimes(1);
});

test('it displays a panel of announcements', async () => {
  useHasNewAnnouncements.mockReturnValue(jest.fn());
  usePimVersion.mockReturnValue({
    data: {edition: 'Serenity', version: '192939349'},
    hasError: false,
  });
  useInfiniteScroll.mockReturnValue([
    {
      items: [],
      isFetching: false,
      hasError: false,
    },
    jest.fn(),
  ]);

  await act(async () => renderDOMWithProviders(<Panel />, container as HTMLElement));

  expect(getByText(container, 'akeneo_communication_channel.panel.title')).toBeInTheDocument();
});

test('it displays an error when it does not get the PIM Version data', async () => {
  usePimVersion.mockReturnValue({
    data: null,
    hasError: true,
  });

  await act(async () => renderDOMWithProviders(<Panel />, container as HTMLElement));

  expect(getByText(container, 'akeneo_communication_channel.panel.list.error')).toBeInTheDocument();
});

test('it can close the panel', async () => {
  await act(async () => renderDOMWithProviders(<Panel />, container as HTMLElement));

  fireEvent.click(getByTitle(container, 'pim_common.close'));

  expect(dependencies.mediator.trigger).toHaveBeenCalledWith('communication-channel:panel:close');
});
