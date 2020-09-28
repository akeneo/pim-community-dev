import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, act, getByText, getByTitle} from '@testing-library/react';
import {Panel} from '@akeneo-pim-community/communication-channel/src/components/panel';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
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

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  expect(getByText(container, 'akeneo_communication_channel.panel.title')).toBeInTheDocument();
  expect(getByText(container, 'akeneo_communication_channel.panel.list.empty')).toBeInTheDocument();
});

test('it displays an error when it does not get the PIM Version data', async () => {
  usePimVersion.mockReturnValue({
    data: null,
    hasError: true,
  });

  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  expect(getByText(container, 'akeneo_communication_channel.panel.list.error')).toBeInTheDocument();
});

test('it can close the panel', async () => {
  await act(async () => renderWithProviders(<Panel />, container as HTMLElement));

  fireEvent.click(getByTitle(container, 'pim_common.close'));

  expect(dependencies.mediator.trigger).toHaveBeenCalledWith('communication-channel:panel:close');
});
