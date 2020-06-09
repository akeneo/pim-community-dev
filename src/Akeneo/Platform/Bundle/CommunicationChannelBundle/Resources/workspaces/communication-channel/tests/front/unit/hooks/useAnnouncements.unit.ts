import '@testing-library/jest-dom/extend-expect';
import {renderHook, act} from '@testing-library/react-hooks';
import {useAnnouncements} from '@akeneo-pim-community/communication-channel/src/hooks/useAnnouncements';

const expectedAnnouncements = [
  {
    title: 'Title announcement',
    description: 'Description announcement',
    img: '/path/img/announcement.png',
    link: 'http://link-announcement.com',
    tags: ['new'],
    date: '20-04-2020',
  },
  {
    title: 'Title announcement 2',
    description: 'Description announcement 2',
    img: '/path/img/announcement-2.png',
    link: 'http://link-announcement-2.com',
    tags: ['new'],
    date: '20-04-2020',
  },
];
const announcementFetcher = {
  fetchAll: () =>
    new Promise(resolve => {
      act(() => {
        setTimeout(() => resolve(expectedAnnouncements), 100);
      });
    }),
};

test('It can get all the announcements', async () => {
  const {result, waitForNextUpdate} = renderHook(() => useAnnouncements(announcementFetcher));

  expect(result.current.announcements).toEqual(null);

  await waitForNextUpdate();

  expect(result.current.announcements).toEqual(expectedAnnouncements);
  expect(typeof result.current.fetchAnnouncements).toBe('function');
});
