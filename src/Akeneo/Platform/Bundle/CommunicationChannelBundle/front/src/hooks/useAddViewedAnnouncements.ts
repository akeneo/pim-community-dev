import {useCallback} from 'react';
import {Announcement} from '../models/announcement';

const useAddViewedAnnouncements = (): ((viewedAnnouncements: Announcement[]) => void) => {
  const handleAddViewedAnnouncements = useCallback(async (viewedAnnouncements: Announcement[]) => {
    const viewedAnnouncementIds = viewedAnnouncements.map((viewedAnnouncement: Announcement) => viewedAnnouncement.id);
    await fetch('/rest/viewed_announcements/add', {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify({
        viewed_announcement_ids: viewedAnnouncementIds,
      }),
    });
  }, []);

  return handleAddViewedAnnouncements;
};

export {useAddViewedAnnouncements};
