import '@testing-library/jest-dom/extend-expect';
import {useAddViewedAnnouncements} from '@akeneo-pim-community/communication-channel/src/hooks/useAddViewedAnnouncements';
import {
  renderHookWithProviders,
  fetchMockResponseOnce,
} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {getExpectedAnnouncements} from '../__mocks__/dataProvider';

const expectedAnnouncements = getExpectedAnnouncements();

afterEach(() => {
  fetchMock.resetMocks();
});

test('it can call the API to add viewed announcements', async () => {
  fetchMockResponseOnce('/rest/viewed_announcements/add', JSON.stringify({}));

  const {result} = renderHookWithProviders(useAddViewedAnnouncements);

  const handleAddViewedAnnouncements = result.current;
  handleAddViewedAnnouncements(expectedAnnouncements);

  expect(fetchMock).toHaveBeenCalledWith('/rest/viewed_announcements/add', {
    body: JSON.stringify({
      viewed_announcement_ids: ['update-title_announcement-20-04-2020', 'update-title_announcement_2-20-04-2020'],
    }),
    headers: [
      ['Content-type', 'application/json'],
      ['X-Requested-With', 'XMLHttpRequest'],
    ],
    method: 'POST',
  });
});
