import '@testing-library/jest-dom/extend-expect';
import {useHasNewAnnouncements} from '@akeneo-pim-community/communication-channel/src/hooks/useHasNewAnnouncements';
import {
  renderHookWithProviders,
  fetchMockResponseOnce,
} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';

afterEach(() => {
  fetchMock.resetMocks();
  sessionStorage.clear();
});

test('it checks if it has new announcements and trigger an event to display coloured dot', async () => {
  const hasNewAnnouncementsResponse = {status: true};
  fetchMockResponseOnce('/rest/new_announcements', JSON.stringify(hasNewAnnouncementsResponse));

  const {result, waitForNextUpdate} = renderHookWithProviders(useHasNewAnnouncements);

  const handleHasNewAnnouncements = result.current;
  handleHasNewAnnouncements();

  await waitForNextUpdate();

  expect(fetchMock).toHaveBeenCalledWith('/rest/new_announcements');
  expect(sessionStorage.getItem('communication_channel_has_new_announcements')).toBe('true');
  expect(dependencies.mediator.trigger).toHaveBeenCalledWith('communication-channel:menu:add_coloured_dot');
});

test('it checks if it does not have new announcements and trigger an event to remove coloured dot', async () => {
  const hasNewAnnouncementsResponse = {status: false};
  fetchMockResponseOnce('/rest/new_announcements', JSON.stringify(hasNewAnnouncementsResponse));

  const {result} = renderHookWithProviders(useHasNewAnnouncements);

  const handleHasNewAnnouncements = result.current;
  handleHasNewAnnouncements();

  expect(fetchMock).toHaveBeenCalledWith('/rest/new_announcements');
  expect(sessionStorage.getItem('communication_channel_has_new_announcements')).toBe('false');
  expect(dependencies.mediator.trigger).toHaveBeenCalledWith('communication-channel:menu:remove_coloured_dot');
});
